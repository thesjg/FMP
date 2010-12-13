package com.flixn.statistics {

    import mx.rpc.http.HTTPService;

    import mx.rpc.events.FaultEvent;
    import mx.rpc.events.ResultEvent;

    public class StatisticsCore extends HTTPService
    {
        internal var _sessionId:String;
        internal var _loadId:String;

        private var _currentMethod:String;
        private var _currentCallback:Function;

        private var _evQueue:Array;
        private var _parameters:Object;

        internal var _settings:Object;

        protected static var _this:Object = null;

        public function StatisticsCore(sessionId:String, settings:Object):void
        {
            super();

            _sessionId = sessionId;
            _loadId = null;

            _currentMethod = null;
            _currentCallback = null;

            _evQueue = new Array();

            useProxy = false;
            method = 'GET';

            _settings = settings;

            addEventListener(FaultEvent.FAULT, httpFault);

//            this.resultFormat = 'text';

            _this = this;
        }

        public function addEvent(method:String, resultCB:Function, params:Object):void
        {
            trace('Statistics: Event added: ' + method);

            _evQueue.push({'method':   method,
                           'callback': resultCB,
                           'params':   params } );

            processQueue();
        }

        public function loadComplete(event:ResultEvent):void
        {
//            trace('LOAD COMPLETE');
//            trace(event.result);
//            for (var i:String in event.result)
//                trace(i + ': ' + event.result[i]);

            _loadId = event.result.loadid;
        }

        public function requestComplete(event:ResultEvent):void
        {
            removeEventListener(ResultEvent.RESULT, requestComplete);

            if (_currentCallback != null) {
//                trace('Statistics: Executing request callback for: ' + _currentMethod);
                _currentCallback(event);
            }

            _currentMethod = null;
            _currentCallback = null;

            processQueue();
        }

        private function processQueue():void
        {
            if (_evQueue.length > 0 && _currentMethod == null) {
                var event:Object = _evQueue.shift();

                _currentMethod = event['method'];
                _currentCallback = event['callback'];

                setupService(event['method']);
                addParameter('sessionId', _sessionId);

                for (var param:String in event.params)
                    if (event.params[param] == null)
//                        addParameter(param, this['_' + param]);
						addParameter(param, null); // wtf was this?
					else
                        addParameter(param, event.params[param]);

                addEventListener(ResultEvent.RESULT, requestComplete);
                sendRequest();
            }
        }

        private function setupService(methodCall:String):void
        {
            trace('Statistics: Preparing query for: ' + methodCall);

            url = _settings.servicesEndpoint + methodCall;

            _parameters = new Object();
            _parameters.requestTime = new Date().getTime();
        }

        private function addParameter(key:String, value:String):void
        {
            _parameters[key] = value;
        }

        private function sendRequest():void
        {
            trace('Statistics: Sending request for: ' + url);
            for (var s:String in _parameters)
                trace('Statistics: Request parameter: ' + s + '=' + _parameters[s]);

            send(_parameters);
        }

        private function httpFault(event:FaultEvent):void
        {
            var faultstring:String = event.fault.faultString;
            trace('Statistics: Fault: ' + faultstring);
        }
    }
}