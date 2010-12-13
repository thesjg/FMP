package com.flixn.settings {

	import com.hurlant.crypto.symmetric.NullPad;
    import mx.core.Application;
    import mx.rpc.events.ResultEvent;
    import mx.rpc.http.HTTPService;

    public class SettingsCore extends HTTPService
    {
		private static var _loadSettings:Boolean = true;
		
        private static var _filePathLocal:String = '../src/';
        private static var _fileProtocol:String  = 'http';
        private static var _fileName:String      = 'settings.xml';
        private static var _fileEndpoint:String  = 'components.flixn.evilprojects.net';

        private static var _servicesEndpoint:String = 'http://api.flixn.evilprojects.net/services/09/rest/';

        private static var _componentId:String;
        private static var _componentKey:String;
        private static var _componentStyle:String;
		
		private static var _sessionId:String;

        private static var singleton:Boolean = false;
        private static var _root:Application;

        public function SettingsCore(root:Application = null):void
        {
            super();

            if (singleton == false && root == null)
                throw new Error('Settings class must be properly initialized prior to use.');

            if (singleton == true)
                return;

            _root = root;

			//_loadSettings = this.retrieveBooleanFlashVar('loadSettings', true);
			_sessionId = this.retrieveStringFlashVar('sessionId', null);

            loadFileSettings();

            singleton = true;
        }
		
		public function get loadSettings():Boolean
		{
			return _loadSettings;
		}
		
		public function set loadSettings(loadSettings:Boolean):void
		{
			_loadSettings = loadSettings;
		}

        public function get servicesEndpoint():String
        {
            return _servicesEndpoint;
        }

        public function get componentId():String
        {
            return _componentId;
        }

        public function get componentKey():String
        {
            return _componentKey;
        }

        public function get componentStyle():String
        {
            return _componentStyle;
        }
		
		public function get sessionId():String
		{
			return _sessionId;
		}
		
		public function set sessionId(sessionId:String):void
		{
			_sessionId = sessionId;
		}

        private function loadFileSettings():void
        {
			if (!_loadSettings)
				return;
			
            var checkUrl:String = _root.url;

//            checkUrl = 'http://components.flixn.evilprojects.net/player-09.swf?cid=489a25da-04b1-0001-f228-95e467836d49';
//            checkUrl = 'http://components.flixn.evilprojects.net/cccccccc-cccc-cccc-cccc-cccccccccccc.swf';

            if (checkUrl.substr(0, 4) == 'file') {
                url = _filePathLocal + _fileName;
            } else {
/*
                var protocol:String = '((?:http|https)://)';
                var hostname:String = '[a-zA-Z0-9\.\-]+';
                var path:String = '[a-zA-Z0-9\-\/]+';
                var uuidPart:String = '([a-f0-9]{8}(-[a-f0-9]{4}){3}-[a-f0-9]{12})';
                var extension:String = '\.swf';
                var rvars:String = '\?.*?';
                var urlPattern:RegExp = new RegExp(protocol + hostname + path + uuidPart + extension + rvars, 'i');
                var matches:Array = checkUrl.match(urlPattern);

                url = matches[1] + _fileEndpoint + '/' + matches[2] + '/' + _fileName;
*/
                var pattern:String = '([a-f0-9]{8}(-[a-f0-9]{4}){3}-[a-f0-9]{12})';
                var reg:RegExp = new RegExp(pattern, 'i');
                var matches:Array = checkUrl.match(reg);

                url = _fileProtocol + '://' + _fileEndpoint + '/' + matches[0] + '/' + _fileName;
            }

            trace('Loading settings from url: ' + this.url);

            useProxy = false;
            method = 'GET';

            var parameters:Object = new Object();
            parameters.requestTime = new Date().getTime();

            addEventListener(ResultEvent.RESULT, settingsFileResult);
            send(parameters);
        }

        protected function retrieveBooleanFlashVar(name:String, defaultValue:Boolean):Boolean
        {
            if (_root.parameters[name] != undefined)
                return _root.parameters[name];
            else
                return defaultValue;
        }

        protected function retrieveNumericFlashVar(name:String, defaultValue:Number):Number
        {
            if (_root.parameters[name] != undefined)
                return _root.parameters[name];
            else
                return defaultValue;
        }

        protected function retrieveStringFlashVar(name:String, defaultValue:String):String
        {
            if (_root.parameters[name] != undefined)
                return _root.parameters[name];
            else
                return defaultValue;
        }

        private function settingsFileResult(event:ResultEvent):void
        {
            _componentId = event.result.settings.id;
            _componentKey = event.result.settings.key;

            _componentStyle = retrieveStringFlashVar('componentStyle', event.result.settings.style);
        }
    }
}