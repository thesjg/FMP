package com.flixn.net 
{
    import com.flixn.browser.BrowserAPI;

	import flash.net.NetConnection;

	public class FXNetConnection extends flash.net.NetConnection
	{	
        private var _browserAPI:BrowserAPI;

        public function FXNetConnection()
        {
            super();

            _browserAPI = new BrowserAPI();
        }

        public function publishSuccess(videoId:String):void
        {
            trace('FlixnRecorderNetConnection: Publish success: Video identifier: ' + videoId);
            _browserAPI.recordComplete(videoId);
        }

        public function publishError(error:String=''):void
        {
            trace('FlixnRecorderNetConnection: Publish error: ' + error);
        }
	}
}