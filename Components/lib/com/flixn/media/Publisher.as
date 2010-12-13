package com.flixn.media
{	
	import com.flixn.services.LiveServices;
	import com.flixn.events.ServicesEvent;

	import com.flixn.interfaces.ISettingsPublishAudioVideo;
	
	import com.flixn.browser.BrowserAPI;
	import com.flixn.media.FXCamera;
	import com.flixn.media.FXMicrophone;
	import com.flixn.net.FXNetConnection;
	import com.flixn.net.FXNetStream;
	import com.flixn.net.Network;
	
	import flash.net.SharedObject;

	import flash.events.NetStatusEvent;
	import flash.events.SyncEvent;
	
	public class Publisher 
	{
		[Bindable]
		public var publishing:Boolean = false;
		
		[Bindable]
		public var started:Boolean = false;
		
		private var _camera:FXCamera = null;
		private var _microphone:FXMicrophone = null;
		
		private var _network:Network;
		private var _settings:ISettingsPublishAudioVideo;
		private var _services:Object;
		private var _browserAPI:BrowserAPI;
		private var _streamsSo:SharedObject;
		
		public function Publisher(settings:ISettingsPublishAudioVideo)
		{
			_settings = settings;

			if (_settings.publishStreamName == null) {
				_services = LiveServices.getInstance();
				_services.addEventListener(ServicesEvent.INIT, haveSessionId);
				_services.initialize();
			}
			
			_network = new Network(_settings);
			
			_browserAPI = new BrowserAPI();
			_streamsSo = null;
		}
		
		public function get netStream():FXNetStream
		{
			return _network.netStream;
		}
		
		public function start(publish:Boolean=true):void
		{
			started = true;
			if (_settings.publishStreamName == null)
				return;
			
			var args:Object = new Object();
			args.name = 'Name';
			
			publishing = publish;
			_network.addEventListener(NetStatusEvent.NET_STATUS, netStatusHandler);
			_network.createNetConnection(_settings.publishStreamName, args);
		}
		
		public function stop():void
		{
			_network.netStream.close();
            publishing = false;
		}

		public function suspendVideo():void
		{
			_network.netStream.attachCamera(null);
		}
		
		public function suspendAudio():void
		{
			_network.netStream.attachAudio(null);
		}
		
		public function resumeVideo():void
		{
            if (_camera !== null && _camera.check() == true)
                _network.netStream.attachCamera(_camera);
		}
		
		public function resumeAudio():void
		{
            if (_microphone !== null && _microphone.check() == true)
                _network.netStream.attachAudio(_microphone);
		}
		
		public function get network():Network
		{
			return _network;
		}
		
		public function set camera(camera:FXCamera):void
		{
			_camera = camera;
		}
			
		public function set microphone(microphone:FXMicrophone):void
		{
			_microphone = microphone;
		}

        public function initiatePublishing():void
		{
			if (publishing)
				trace('Already Publishing!');
			
            _network.createNetStream();

            if (_camera !== null && _camera.check() == true)
                _network.netStream.attachCamera(_camera);

            if (_microphone !== null && _microphone.check() == true)
                _network.netStream.attachAudio(_microphone);
	
			publish();

			publishing = true;
        }

		private function haveSessionId(event:ServicesEvent):void
		{
			_settings.publishStreamName = _services.sessionId;
			if (started)
				start();
		}

		private function netStatusHandler(event:NetStatusEvent):void
        {
            switch (event.info.code) {
                case 'NetConnection.Connect.Success':
                    trace('NetConnection Status: Success');					
					// YYY: Move this up a layer?
					var nc:FXNetConnection = _network.netConnection;
					_streamsSo = SharedObject.getRemote('streams', nc.uri, false);
					_streamsSo.addEventListener(SyncEvent.SYNC, onStreamsSoSync);
					_streamsSo.connect(nc);
					if (publishing)
						initiatePublishing();
                    break;
                case 'NetConnection.Connect.Failed':
                    trace('NetConnection Status: Failed');
					if (publishing)
						start();
                	break;
                case 'NetConnection.Connect.Closed':
                    trace('NetConnection Status: Closed');
					if (publishing)
						start();
                	break;
                case 'NetConnection.Connect.Rejected':
                    trace('NetConnection Status: Rejected');
                    break;
				case 'NetStream.Publish.Start':
					trace('NetStream Status: Publish Start');
					break;
				case 'NetStream.Publish.Stop':
					trace('NetStream Status: Publish Stop');
					break;
				default:
                    trace('Unhandled Network Event: ' + event.info.code);
            }
        }

		private function publish():void
		{
			if (publishing)
				return;

			if (_settings.publishStreamName == '')
				return;

			_network.netStream.publish(_settings.publishStreamName, 'live');
		}
		
		/**
		 * Support function for Live components, to handle effective joins/parts
		 * generated by server-maintained shared object
		 * 
		 * @param	event
		 */
		private function onStreamsSoSync(event:SyncEvent):void
		{			
			trace('onStreamsSoSync');

			import mx.utils.ObjectUtil;
			trace(ObjectUtil.toString(event));

			for (var x:String in event.changeList) {
				var code:String = event.changeList[x].code;
				var name:String = event.changeList[x].name;
				
				if (name == null || name == 'null')
					continue;
				
				if (code == 'success' || code == 'change') {
					if (name != _settings.publishStreamName)
						_browserAPI.liveStreamCreated(_settings.publishAppInstanceName, name);
				} else if (code == 'clear' || code == 'delete') {
					trace('XXX: Caught a clear or delete');
					if (name != _settings.publishStreamName) {
						trace('XXX: Destroying stream');
						_browserAPI.liveStreamDestroyed(_settings.publishAppInstanceName, name);
					}
				}
			}
		}
	}
}