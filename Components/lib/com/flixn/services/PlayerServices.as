package com.flixn.services {

    import com.flixn.settings.SettingsCore;

    import mx.controls.*;
    import flash.errors.*;

    import mx.rpc.http.HTTPService;
    import mx.rpc.events.ResultEvent;

    public class PlayerServices extends ServicesCore
    {
        private static var _mediaId:String;

        public function PlayerServices():void
        {
            super();
        }

        public static function getInstance():Object
        {
            if (_this != null)
                return _this;

            new PlayerServices();
            return _this;
        }

        public function playGetMedia(mediaId:String=null):void
        {
            if (mediaId !== null)
                _mediaId = mediaId;

            _executing = playGetMedia;
            if (initialize() == false)
                return;

            setupService('mediaGetMedia');

            addParameter('sessionId', _sessionId);
            //addParameter('instanceId', _settings.componentId);
            addParameter('mediaId', _mediaId);

            addEventListener(ResultEvent.RESULT, playGetMediaResult);
            sendRequest();
        }

        public function playSMSMedia(phoneNumber:String):void
        {
            _executing = playGetMedia;
            if (initialize() == false)
                return;

            setupService('mediaSmsMedia');

            addParameter('sessionId', _sessionId);
            addParameter('mediaId', _mediaId);
            addParameter('phoneNumber', phoneNumber);

            sendRequest();
        }

        private function playGetMediaResult(event:ResultEvent):void
        {
            removeEventListener(ResultEvent.RESULT, playGetMediaResult);

            dispatchEvent(new ServicesEvent(ServicesEvent.MEDIA,
                                            true,
                                            false,
                                            event.message.body));
        }
    }
}