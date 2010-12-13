/* XXX: Reimplement using servicescore */
package com.flixn.services {

    /**
    *
    *
    * @author Flixn, Inc.
    * @version 0.9
    */

    import mx.controls.*;
    import flash.errors.*;

    import flash.events.*;

    import mx.rpc.http.HTTPService;
    import mx.rpc.events.*;

    import com.flixn.uploader.model.*;

    public class UploaderServices extends ServicesCore
    {
        private const ENDPOINT:String = 'http://api.flixn.evilprojects.net/services/09/rest/';

        private var _settings:Settings;
        private var _service:HTTPService;

        private var _sessionId:String;

        private var _initializing:Boolean = false;
        private var _initialized:Boolean = false;

        private var _executing:Function;

        public function Services(app:Object):void
        {
            //_settings = new Settings();
            _settings = app.settings;
        }

        public function uploadGetTicket():void
        {
            trace('uploadGetTicket()');

            _executing = uploadGetTicket;
            if (initialize() == false)
                return;

            createService();

//_service.resultFormat = 'text';

            _service.url = ENDPOINT + 'uploadGetTicket';
            var parameters:Object = new Object();
            parameters.sessionId   = _sessionId;
            parameters.instanceId  = _settings.componentId;
            parameters.requestTime = new Date().getTime();

            _service.addEventListener(ResultEvent.RESULT, uploadGetTicketResult);
            _service.send(parameters);
        }

        public function uploadGetLimits():void
        {
            _executing = uploadGetLimits;
            if (initialize() == false)
                return;

        }

        private function initialize():Boolean
        {
            trace('initialize()');

            if (_initialized == true)
                return true;

            if (_initializing == true)
                return false;

            _initializing = true;

            sessionInitiate();

            return false;
        }

        private function createService():void
        {
            _service = new HTTPService();
            _service.useProxy = false;
            _service.method = 'GET';

//_service.resultFormat = 'text';

            _service.addEventListener(FaultEvent.FAULT, httpFault);
        }

        private function sessionInitiate():void
        {
            trace('sessionInitiate()');

            createService();

            _service.url = ENDPOINT + 'sessionInitiate';

            var parameters:Object = new Object();
            parameters.requestTime = new Date().getTime();

            _service.addEventListener(ResultEvent.RESULT, sessionInitiateResult);
            _service.send(parameters);
        }

        private function sessionAuthenticate():void
        {
            trace('sessionAuthenticate()');

            createService();

            _service.url = ENDPOINT + 'sessionAuthenticate';

            var parameters:Object = new Object();
            trace('sessionId: ' + _sessionId);
            trace('apiKey: ' + _settings.componentKey);
            parameters.sessionId = _sessionId;
            parameters.apiKey    = _settings.componentKey;
            parameters.requestTime = new Date().getTime();

//_service.resultFormat = 'text';

            _service.addEventListener(ResultEvent.RESULT, sessionAuthenticateResult);
            _service.send(parameters);
        }

        private function uploadGetTicketResult(event:ResultEvent):void
        {
            dispatchEvent(new ServicesEvent(ServicesEvent.TICKET,
                                            true,
                                            false,
                                            event.result));
        }

        private function uploadGetLimitsResult(event:ResultEvent):void
        {
            trace('XXX4: ' + event.result.toString());

            dispatchEvent(new ServicesEvent(ServicesEvent.LIMITS,
                                            true,
                                            false,
                                            event.result));
        }

        private function sessionInitiateResult(event:ResultEvent):void
        {
            trace('XXX1: ' + event.result.toString());

            _sessionId = event.result.sessionid;

            sessionAuthenticate();
        }

        private function sessionAuthenticateResult(event:ResultEvent):void
        {
            trace('XXX2: ' + event.result.toString());

            if (event.result.authenticated == true) {
                _initialized = true;
                _executing();
            } else {
                trace('SAXXX: ' + event.result.authenticated);
                trace('XXX: ERROR');
            }
        }

        private function httpFault(event:FaultEvent):void
        {
            var faultstring:String = event.fault.faultString;
            trace(faultstring);
        }
    }
}