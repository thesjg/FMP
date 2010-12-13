package com.flixn.services {

    import com.flixn.settings.SettingsCore;
	import com.flixn.events.ServicesEvent;
	
    import mx.controls.*;
    import flash.errors.*;

    import mx.rpc.http.HTTPService;
    import mx.rpc.events.ResultEvent;

    public class LiveServices extends ServicesCore
    {
        public function LiveServices():void
        {
            super();
        }

        public static function getInstance():Object
        {
            if (_this != null)
                return _this;

            new LiveServices();
			
            return _this;
        }
		
		public function get sessionId():String
		{
			return _sessionId;
		}
	}
}