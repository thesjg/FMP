package com.flixn.services {

    import com.flixn.settings.SettingsCore;
	import com.flixn.events.ServicesEvent;

    import mx.controls.*;
    import flash.errors.*;

    import mx.rpc.http.HTTPService;
    import mx.rpc.events.ResultEvent;

    public class RecorderServices extends ServicesCore
    {
		internal static var _ticketId:String = null;
		
        public function RecorderServices():void
        {
            super();
        }

        public static function getInstance():RecorderServices
        {
            if (_this != null)
                return RecorderServices(_this);

            new RecorderServices();

            return RecorderServices(_this);
        }
		
		public function get sessionId():String
		{
			return _sessionId;
		}

        public function recordGetTicket():void
        {
            _executing = recordGetTicket;
            if (initialize() == false)
                return;

            setupService('recordGetTicket');

            addParameter('sessionId', _sessionId);
            addParameter('instanceId', _settings.componentId);

            addEventListener(ResultEvent.RESULT, recordGetTicketResult);
            sendRequest();
        }

        public function recordGetLimits():void
        {
            _executing = recordGetLimits;
            if (initialize() == false)
                return;

            setupService('recordGetLimits');

            addParameter('ticketId', _ticketId);

            addEventListener(ResultEvent.RESULT, recordGetLimitsResult);
            sendRequest();
        }

        private function recordGetTicketResult(event:ResultEvent):void
        {
			_ticketId = event.result.ticketid;
			
            dispatchEvent(new ServicesEvent(ServicesEvent.TICKET,
                                            true,
                                            false,
                                            event.result));
        }

        private function recordGetLimitsResult(event:ResultEvent):void
        {
            dispatchEvent(new ServicesEvent(ServicesEvent.LIMITS,
                                            true,
                                            false,
                                            event.result));
        }
    }
}