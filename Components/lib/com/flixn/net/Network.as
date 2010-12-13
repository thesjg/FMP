package com.flixn.net
{
	import com.flixn.net.FXNetConnection;
	import com.flixn.net.FXNetStream;
    import flash.net.ObjectEncoding;

    import flash.events.Event;
    import flash.events.EventDispatcher;
    import flash.events.IEventDispatcher;
    import flash.events.NetStatusEvent;
    import flash.events.SecurityErrorEvent;
    import flash.events.IOErrorEvent;
    import flash.events.AsyncErrorEvent;

    import mx.controls.Alert;

    public class Network extends EventDispatcher {

        private static var _nc:FXNetConnection = null;
        private static var _ns:FXNetStream = null;
		
		private var _settings:Object;

        public function Network(settings:Object=null) {
			_settings = settings;
        }

        public function get netConnection():FXNetConnection
        {
            return _nc;
        }

        public function get netStream():FXNetStream
        {
            return _ns;
        }

        public function setupNetConnection(host:String, args:Array):void
        {
            trace('Network: Creating NetConnection');

            _nc = new FXNetConnection();

			_nc.objectEncoding = ObjectEncoding.AMF0;

            _nc.addEventListener(NetStatusEvent.NET_STATUS, dispatchEvent);
            _nc.addEventListener(AsyncErrorEvent.ASYNC_ERROR, ncAsyncErrorHandler);
            _nc.addEventListener(IOErrorEvent.IO_ERROR, ncIOErrorHandler);
            _nc.addEventListener(SecurityErrorEvent.SECURITY_ERROR, ncSecurityErrorHandler);

			if (args.length > 0)
				_nc.connect(host, args);
            else
				_nc.connect(host, null);
        }

        public function createNetConnection(... args:Array):void
        {
            trace('Network: Connecting to: rtmp://' + _settings.publishEndpoint);

			if (args.length > 0)
				setupNetConnection('rtmp://' + _settings.publishEndpoint, args);
			else
				setupNetConnection('rtmp://' + _settings.publishEndpoint, null);
        }

        public function closeNetConnection():void
        {
            _nc.close();
        }

        public function createNetStream():void
        {
            trace('Network: Creating NetStream');

            _ns = new FXNetStream(_nc);

            _ns.addEventListener(NetStatusEvent.NET_STATUS, dispatchEvent);
            _ns.addEventListener(AsyncErrorEvent.ASYNC_ERROR, nsAsyncErrorHandler);
            _ns.addEventListener(SecurityErrorEvent.SECURITY_ERROR, nsSecurityErrorHandler);

            //_netSt.client = this;
            //dispatchEvent(new Event("onVideoReady"));
        }

        private function ncAsyncErrorHandler(event:AsyncErrorEvent):void
        {
            Alert.show('NetConnection: asyncErrorHandler: ' + event);
        }

        private function ncIOErrorHandler(event:AsyncErrorEvent):void
        {
            Alert.show('NetConnection: ioErrorHandler: ' + event);
        }

        private function ncSecurityErrorHandler(event:SecurityErrorEvent):void
        {
            Alert.show('NetConnection: securityErrorHandler: ' + event);
        }

        private function nsAsyncErrorHandler(event:AsyncErrorEvent):void
        {
            Alert.show('NetStream: asyncErrorHandler: ' + event);
        }

        private function nsSecurityErrorHandler(event:SecurityErrorEvent):void
        {
            Alert.show('NetStream: securityErrorHandler: ' + event);
        }
    }
}