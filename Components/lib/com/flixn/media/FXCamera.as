package com.flixn.media 
{
	import com.flixn.interfaces.ISettingsVideo;
	import com.flixn.events.FXCameraEvent;
	
	import flash.events.Event;
	import flash.events.ActivityEvent;
	import flash.events.IEventDispatcher;
	
	import flash.media.Camera;
	import flash.net.SharedObject;
	
	public class FXCamera implements IEventDispatcher
	{
		private var _settings:ISettingsVideo;
		private var _camera:flash.media.Camera;
		
		private var _cameraOpen:Boolean = false;
		
		private var _checkModes:Array = [[1600, 1200], [1280, 960], [1024, 768],
										 [800, 600],   [640, 480],  [320, 240]];
		private var _currentCheckMode:int;
		private var _modes:Array = [];
		private var _currentMode:int;
		private var _determiningModes:Boolean = false;
		
		public function FXCamera(settings:ISettingsVideo)
		{
			_settings = settings;
		}
		
		public function open(name:String = null):Boolean
		{
			if (check() != true)
				return false;
			
			_camera = flash.media.Camera.getCamera(name);
			_cameraOpen = true;
			
			return true; /* XXX */
		}
		
		public function check():Boolean
		{
            var ret:Boolean = false;

            if (_settings.videoEnable == true)
                ret = (flash.media.Camera.names.length) ? true : false;

            if (ret == true)
                trace('Camera: Using webcam');
            else
                trace('Camera: Not using webcam/unavailable');

            return ret;
		}
		
		public function setup():Boolean
		{
			if (_cameraOpen == false || _determiningModes == true)
				return false;

			setMode(_settings.videoWidth, _settings.videoHeight, _settings.videoFrameRate);
			setQuality(0, _settings.videoQuality);
			
			return true;
		}
		
		/**
		 * Should be called prior to setup() or being made visible
		 */
		public function determineModes():void
		{
			if (_determiningModes == true)
				return;
				
			if (_modes.length > 0 && _determiningModes == false) {
				dispatchEvent(new FXCameraEvent(FXCameraEvent.MODES_COMPLETE, true, false, _modes));
				return;
			}

			var so:SharedObject = SharedObject.getLocal('cameraModes');
			if (so.size > 0) {
				_modes = so.data.modes as Array;
				dispatchEvent(new FXCameraEvent(FXCameraEvent.MODES_COMPLETE, true, false, _modes));
				return;
			}

			_currentCheckMode = 0;
			_determiningModes = true;
			
			setMotionLevel(0, 10000);
			addEventListener(ActivityEvent.ACTIVITY, modeActivityEventHandler);
			
			dispatchEvent(new FXCameraEvent(FXCameraEvent.MODES_BEGIN, true, false, _modes));

			modeActivityEventHandler();
		}
		
		public function setMode(width:int, height:int, fps:Number, favorArea:Boolean = true):void
		{
			return _camera.setMode(width, height, fps, favorArea);
		}
		
		public function setMotionLevel(motionLevel:int, timeout:int = 2000):void
		{
			return _camera.setMotionLevel(motionLevel, timeout);
		}
		
		public function setQuality(bandwidth:int, quality:int):void
		{
			return _camera.setQuality(bandwidth, quality);
		}

		public function get camera():flash.media.Camera
		{
			return _camera;
		}
		
		public function get modes():Array
		{
			return _modes;
		}
		
		private function modeActivityEventHandler(event:ActivityEvent = null):void
		{
			if (event != null && event.activating == false)
				return;
			
			if (event == null || event.activating) {
				if (event != null) {
					
					var hasMode:Boolean = false;
					for (var i:uint = 0; i < _modes.length; i++) {
						if (_modes[i][0] == _camera.width && _modes[i][1] == _camera.height) {
							hasMode = true;
							break;
						}
					}
				
					_currentCheckMode++;

					if (hasMode == false)
						_modes.push([_camera.width, _camera.height]);

					hasMode = false;
					if (_currentCheckMode < _checkModes.length) {
						for (i = 0; i < _modes.length; i++) {
							if (_modes[i][0] == _checkModes[_currentCheckMode][0] &&
								_modes[i][1] == _checkModes[_currentCheckMode][1])
							{
								modeActivityEventHandler();
							}
						}
					}
				}
				
				if (_currentCheckMode >= _checkModes.length) {
					removeEventListener(ActivityEvent.ACTIVITY, modeActivityEventHandler);
					_determiningModes = false;
					
					var so:SharedObject = SharedObject.getLocal('cameraModes');
					if (so.size == 0)
						so.data.modes = _modes;

					dispatchEvent(new FXCameraEvent(FXCameraEvent.MODES_COMPLETE, true, false, _modes));
					
					return;
				}
				
				setMode(_checkModes[_currentCheckMode][0], _checkModes[_currentCheckMode][1], 1);
			}
		}
		
		/* IEventDispatcher passthrough */
		
		public function addEventListener(type:String, listener:Function, useCapture:Boolean = false,
		                                 priority:int = 0, useWeakReference:Boolean = false):void
		{
			return _camera.addEventListener(type, listener, useCapture, priority, useWeakReference);
		}
		
		public function dispatchEvent(event:Event):Boolean
		{
			return _camera.dispatchEvent(event);
		}
		
		public function hasEventListener(type:String):Boolean
		{
			return _camera.hasEventListener(type);
		}
		
		public function removeEventListener(type:String, listener:Function, useCapture:Boolean = false):void
		{
			return _camera.removeEventListener(type, listener, useCapture);
		}

		public function willTrigger(type:String):Boolean
		{
			return _camera.willTrigger(type);
		}
	}
}