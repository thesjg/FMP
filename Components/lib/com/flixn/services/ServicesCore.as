package com.flixn.services {

    import com.flixn.settings.SettingsCore;
	import com.flixn.events.ServicesEvent;

    import mx.rpc.http.HTTPService;
    import mx.rpc.events.FaultEvent;
    import mx.rpc.events.ResultEvent;

    public class ServicesCore extends HTTPService
    {
        internal static var _settings:SettingsCore;
        internal static var _this:Object = null;

        internal var _initializing:Boolean = false;
        internal var _initialized:Boolean = false;
        internal var _executing:Function = null;

        internal var _parameters:Object;

        internal var _sessionId:String;

        public function ServicesCore():void
        {
            super();
            _settings = new SettingsCore();
            _this = this;
        }

        public static function getInstance():Object
        {
            if (_this != null)
                return _this;

            new ServicesCore();
            return _this;
        }

        public function initialize():Boolean
        {
            trace('Services: Initializing');

            if (_initialized == true)
                return true;

            if (_initializing == true)
                return false;

            _initializing = true;

            sessionInitiate();

            return false;
        }

        public function setupService(methodCall:String):void
        {
            trace('Services: Preparing query for: ' + methodCall);

            useProxy = false;
            method = 'GET';
            url = _settings.servicesEndpoint + methodCall;

            _parameters = new Object();
            _parameters.requestTime = new Date().getTime();

            addEventListener(FaultEvent.FAULT, httpFault);
        }

        public function addParameter(key:String, value:String):void
        {
            _parameters[key] = value;
        }

        public function sendRequest():void
        {
//            trace('Services: Sending request');
//            for (var s:String in _parameters)
//                trace('Services: Request parameter: ' + s + '=' + _parameters[s]);

            send(_parameters);
        }

        public function sessionInitiate():void
        {
            setupService('sessionInitiate');

            addEventListener(ResultEvent.RESULT, sessionInitiateResult);
            sendRequest();
        }

        public function sessionAuthenticate():void
        {
            setupService('sessionAuthenticate');

            addParameter('sessionId', _sessionId);
            addParameter('apiKey', _settings.componentKey);

            addEventListener(ResultEvent.RESULT, sessionAuthenticateResult);
            sendRequest();
        }

        private function sessionInitiateResult(event:ResultEvent):void
        {
            removeEventListener(ResultEvent.RESULT, sessionInitiateResult);

            // TODO: Check for errors
            trace('SID: ' + event.result.sessionid);

			_settings.sessionId = event.result.sessionid;
            _sessionId = event.result.sessionid;

            dispatchEvent(new ServicesEvent(ServicesEvent.INIT,
                                            true,
                                            false,
                                            event.result));

            sessionAuthenticate();
        }

        private function sessionAuthenticateResult(event:ResultEvent):void
        {
            removeEventListener(ResultEvent.RESULT, sessionAuthenticateResult);

            trace(event.message.body);

            if (event.result.authenticated == true) {
                _initialized = true;
                if (_executing != null)
                    _executing();
            } else {
                trace('SAXXX: ' + event.result.authenticated);
            }
        }

        private function httpFault(event:FaultEvent):void
        {
            var faultstring:String = event.fault.faultString;
            trace(faultstring);
        }
    }
}