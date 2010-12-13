package com.flixn.media
{
	import com.flixn.interfaces.ISettingsPublishAudioVideo;
	import com.flixn.services.RecorderServices;
	import com.flixn.events.ServicesEvent;
	
	import com.flixn.browser.BrowserAPI;
	import com.flixn.media.FXCamera;
	import com.flixn.media.FXMicrophone;
	import com.flixn.net.FXNetConnection;
	import com.flixn.net.FXNetStream;
	import com.flixn.net.Network;

	import flash.events.NetStatusEvent;
	import flash.events.SyncEvent;
	
	public class Recorder
	{
		[Bindable]
		public var recording:Boolean = false;

		[Bindable]
		public var started:Boolean = false;

		private var _camera:FXCamera = null;
		private var _microphone:FXMicrophone = null;

		private var _network:Network = null;
		private var _settings:ISettingsPublishAudioVideo;
		private var _services:RecorderServices;
		private var _browserAPI:BrowserAPI;

		public function Recorder(settings:ISettingsPublishAudioVideo)
		{
			_settings = settings;

			_browserAPI = new BrowserAPI();
		}

		public function get netStream():FXNetStream
		{
			return _network.netStream;
		}

		public function start():void
		{
			if (started == true)
				return;

			if (_settings.publishStreamName == null) {
				_services = RecorderServices.getInstance();
				_services.addEventListener(ServicesEvent.INIT, haveSessionId);
				_services.initialize();
			}

			if (_network == null) {
				_network = new Network(_settings);
				_network.addEventListener(NetStatusEvent.NET_STATUS, netStatusHandler);
				_network.createNetConnection(_settings.publishStreamName);
			}

			started = true;
		}
		
		public function stop():void
		{
			_network.netStream.attachAudio(null);
			_network.netStream.attachCamera(null);
            recording = false;
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

		public function doSave():void
		{
            _network.netConnection.call('FxPublishMedia', null, '', _settings.publishStreamName);
		}

        public function initiateRecording():void
		{
			if (recording)
				return;

            _network.createNetStream();

            if (_camera !== null && _camera.check() == true)
                _network.netStream.attachCamera(_camera);

            if (_microphone !== null && _microphone.check() == true)
                _network.netStream.attachAudio(_microphone);
	
			record();

			recording = true;
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
                    break;
                case 'NetConnection.Connect.Failed':
                    trace('NetConnection Status: Failed');
					if (recording)
						start();
                	break;
                case 'NetConnection.Connect.Closed':
                    trace('NetConnection Status: Closed');
					if (recording)
						start();
                	break;
                case 'NetConnection.Connect.Rejected':
                    trace('NetConnection Status: Rejected');
                    break;
				case 'NetStream.Record.Start':
					trace('NetStream Status: Record Start');
					break;
				case 'NetStream.Record.Stop':
					trace('NetStream Status: Record Stop');
					break;
				case 'NetStream.Buffer.Empty':
					trace('NetStream Status: Buffer Empty');
					if (_settings.recordHighQuality && !recording)
						_network.netStream.close();
					break;
				default:
                    trace('Unhandled Network Event: ' + event.info.code);
            }
        }

		/* Number of times the user has recorded / re-recorded this session */
		private static var _pCount:Number = 0;
		
		private function record():void
		{
			if (recording)
				return;
			
			if (_settings.publishStreamName == '')
				return;

			if (_settings.recordHighQuality == true)
				_network.netStream.bufferTime = _settings.recordDuration * 10;
	
			_pCount++;
	
			if (_settings.publishStreamName.length > 36)
				_settings.publishStreamName = _settings.publishStreamName.substr(0, 36);
			
			_settings.publishStreamName = _settings.publishStreamName + '-' + _pCount;
			trace('Recorder: publishing ' + _settings.publishStreamName);
			_network.netStream.publish(_settings.publishStreamName, 'record');
		}
	}
}