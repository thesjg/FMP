package com.flixn.statistics {

    import com.flixn.recorder.model.Settings;

    import mx.rpc.events.ResultEvent;

    public class RecorderStatistics extends StatisticsCore {

        internal var _recordId:String;

        public function RecorderStatistics(sessionId:String)
        {
            var settings:Settings = new Settings();
            super(sessionId, settings);

            _recordId = null;
        }

        public static function getInstance():Object
        {
            return _this;
        }

        public static function addLoad():void
        {
/*
            addEvent('statisticsAddLoad',
                     loadComplete,
                     {'instanceId': _settings.componentId,
                      'url': 'xxx'});
*/
        }

        public static function addRecord():void
        {
/*
            addEvent('statisticsAddRecord',
                     recordComplete,
                     {'loadId': _loadId});
*/
        }

        public static function addRecordView():void
        {
/*
            addEvent('statisticsAddRecordReview',
                     null,
                     {'recordId': _recordId});
*/
        }

        public static function addRecordComplete():void
        {
/*
            addEvent('statisticsAddRecordComplete',
                     null,
                     {'recordId': _recordId});
*/
		}

        private static function recordComplete(event:ResultEvent):void
        {
            //_recordId = event.result.recordid;
        }
    }
}