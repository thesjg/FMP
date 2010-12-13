package com.flixn.statistics {

    import com.flixn.uploader.model.Settings;

    import mx.rpc.events.ResultEvent;

    public class UploaderStatistics extends StatisticsCore {

        public function UploaderStatistics(sessionId:String)
        {
            var settings:Settings = new Settings();
            super(sessionId, settings);
        }

        public static function getInstance():Object
        {
            return _this;
        }
    }
}