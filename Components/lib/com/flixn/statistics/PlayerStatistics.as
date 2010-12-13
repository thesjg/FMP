package com.flixn.statistics {

    import com.flixn.player.model.Settings;

    import mx.rpc.events.ResultEvent;

    public class PlayerStatistics extends StatisticsCore {

        public function PlayerStatistics(sessionId:String)
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