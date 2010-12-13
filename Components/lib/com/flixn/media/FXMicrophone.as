package com.flixn.media 
{
	import com.flixn.interfaces.ISettingsAudio;
	import com.flixn.net.FXNetConnection;
	import com.flixn.net.FXNetStream;
	
	import flash.events.IEventDispatcher;
	import flash.events.Event;
	import flash.media.Microphone;
	
	public class FXMicrophone implements IEventDispatcher
	{
		private var _settings:ISettingsAudio;
		
		private var _microphone:flash.media.Microphone;
		
		private var _nc:FXNetConnection;
		private var _ns:FXNetStream;
		
		public function FXMicrophone(settings:ISettingsAudio) 
		{
			_settings = settings;
		}
		
		public function open(index:int = 0):Boolean
		{
			if (check() != true)
				return false;
			
			_microphone = flash.media.Microphone.getMicrophone(index);
			setup();
			
			return true; /* XXX */
		}
		
		public function check():Boolean
		{
            var ret:Boolean = false;

            if (_settings.audioEnable == true)
                ret = (flash.media.Microphone.names.length) ? true : false;

            if (ret == true)
                trace('Microphone: Using microphone');
            else
                trace('Microphone: Not using microphone/unavailable');

            return ret;
		}
		
		public function setup():void
		{
			_microphone.rate = _settings.audioRate;
            _microphone.setUseEchoSuppression(_settings.audioUseEchoSuppression);

			/*
			 * Setup a bunk NetStream and attach audio to it when we do
			 * Microphone setup so that we get the appropriate security
			 * alert immediately
			 */
			_nc = new FXNetConnection();
			_nc.connect(null);
			_ns = new FXNetStream(_nc);
            _ns.attachAudio(this);
			//_ns.attachAudio(null); XXX: Needed?
		}
		
		public function get microphone():flash.media.Microphone
		{
			return _microphone;
		}
		
		/* IEventDispatcher passthrough */
		
		public function addEventListener(type:String, listener:Function, useCapture:Boolean = false,
		                                 priority:int = 0, useWeakReference:Boolean = false):void
		{
			return _microphone.addEventListener(type, listener, useCapture, priority, useWeakReference);
		}
		
		public function dispatchEvent(event:Event):Boolean
		{
			return _microphone.dispatchEvent(event);
		}
		
		public function hasEventListener(type:String):Boolean
		{
			return _microphone.hasEventListener(type);
		}
		
		public function removeEventListener(type:String, listener:Function, useCapture:Boolean = false):void
		{
			return _microphone.removeEventListener(type, listener, useCapture);
		}

		public function willTrigger(type:String):Boolean
		{
			return _microphone.willTrigger(type);
		}
	}
}