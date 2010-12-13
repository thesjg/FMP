package com.flixn.browser {

    import flash.external.ExternalInterface;

    public class BrowserAPI
    {
        public static var loadCompleteMethod:String;
        public static var recordCompleteMethod:String;
        public static var uploadCompleteMethod:String;
        public static var uploadCompleteFileMethod:String;
		
		public static var snapshotBeginMethod:String;
        public static var snapshotCompleteMethod:String;
		
		public static var liveStreamCreatedMethod:String;
		public static var liveStreamDestroyedMethod:String;
		
		private static var _lightsLowered:Boolean;

        private static var _this:Object = null;

        public function BrowserAPI()
        {
            if (_this !== null)
                return;

            BrowserAPI.loadCompleteMethod        = 'flixn.loadComplete';
            
			BrowserAPI.recordCompleteMethod      = 'flixn.recordComplete';
            
			BrowserAPI.uploadCompleteMethod      = 'flixn.uploadComplete';
            BrowserAPI.uploadCompleteFileMethod  = 'flixn.uploadCompleteFile';
			
			BrowserAPI.snapshotBeginMethod       = 'flixn.snapshotBegin';
			BrowserAPI.snapshotCompleteMethod    = 'flixn.snapshotComplete';
			
			BrowserAPI.liveStreamCreatedMethod   = 'flixn.liveStreamCreated';
			BrowserAPI.liveStreamDestroyedMethod = 'flixn.liveStreamDestroyed';
			
            BrowserAPI._lightsLowered = false;

            _this = this;
        }

        public function get lightsLowered():Boolean
        {
            return _lightsLowered;
        }

        public function getURL():String
        {
            return ExternalInterface.call('window.location.href.toString');
        }

        public function loadComplete():void
        {
            if (BrowserAPI.loadCompleteMethod !== null && ExternalInterface.available)
                ExternalInterface.call(BrowserAPI.loadCompleteMethod);
        }

        public function recordComplete(mediaId:String):void
        {
            if (BrowserAPI.recordCompleteMethod !== null && ExternalInterface.available)
                ExternalInterface.call(BrowserAPI.recordCompleteMethod, mediaId);
        }

        public function uploadCompleteFile(mediaId:String):void
        {
            if (BrowserAPI.uploadCompleteMethod !== null && ExternalInterface.available)
                ExternalInterface.call(BrowserAPI.uploadCompleteFileMethod, mediaId);
        }

        public function uploadComplete(media:Array):void
        {
            if (BrowserAPI.uploadCompleteMethod !== null && ExternalInterface.available)
                ExternalInterface.call(BrowserAPI.uploadCompleteMethod, media);
        }
		
		public function snapshotBegin():void
		{
			if (BrowserAPI.snapshotBeginMethod !== null && ExternalInterface.available)
				ExternalInterface.call(BrowserAPI.snapshotBeginMethod);
		}
		
		public function snapshotComplete():void
		{
			if (BrowserAPI.snapshotCompleteMethod !== null && ExternalInterface.available)
				ExternalInterface.call(BrowserAPI.snapshotCompleteMethod);
		}

		public function liveStreamCreated(instanceName:String, streamName:String):void
		{
			if (BrowserAPI.liveStreamCreatedMethod !== null && ExternalInterface.available)
				ExternalInterface.call(BrowserAPI.liveStreamCreatedMethod, instanceName, streamName);
		}
		
		public function liveStreamDestroyed(instanceName:String, streamName:String):void
		{
			if (BrowserAPI.liveStreamDestroyedMethod !== null && ExternalInterface.available)
				ExternalInterface.call(BrowserAPI.liveStreamDestroyedMethod, instanceName, streamName);
		}
		
        public function createPopup(url:String, width:int=800, height:int=600):void
        {
var jsfunc:String = (<![CDATA[
function() {
    window.open('${url}', '_blank', 'width=${width},height=${height},location=no,menubar=no,status=no,toolbar=no');
}
]]>).toString();

            jsfunc = jsfunc.replace('${url}', url);
            jsfunc = jsfunc.replace('${width}', width);
            jsfunc = jsfunc.replace('${height}', height);

            if (ExternalInterface.available)
                ExternalInterface.call(jsfunc);
        }

        public function lowerLights():void
        {
            if (_lightsLowered == true)
                return;

var jsfunc:String = (<![CDATA[
function() {
    var backdropId = 'flixn_fmp_player_backdrop';
    var backdropColor = '#000000';
    var backdropOpacity = 60;

    var backdrop = document.getElementById(backdropId);
    if (backdrop != null)
        return;

    backdrop = document.createElement('div');
    backdrop.setAttribute('id', backdropId);

    var width, height;

    if (window.innerWidth) {
        width = window.innerWidth;
        height = window.innerHeight;
    }

    if (document.documentElement &&
        document.documentElement.clientWidth) {

        cw = document.documentElement.clientWidth;
        if (!width || cw && cw < width) {
            width = cw;
        }
        if (!height) {
            height = document.documentElement.clientHeight;
        }
    } else if (document.body) {
        width = document.body.clientWidth;
        height = document.body.clientHeight;
    }

    height = Math.max(document.documentElement.scrollHeight ||
                      document.body.scrollHeight, height);

    backdrop.style.width = width + 'px';
    backdrop.style.height = height + 'px';
    backdrop.style.backgroundColor = backdropColor;
    backdrop.style.position = 'absolute';
    backdrop.style.zIndex = 9998;
    backdrop.style.top = 0;
    backdrop.style.left = 0;

    backdrop.style.MozOpacity = backdropOpacity / 100;
    backdrop.style.opacity = backdropOpacity / 100;
    backdrop.style.filter = 'alpha(opacity=' + backdropOpacity + ')';

    document.body.appendChild(backdrop);
}
]]>).toString();

            if (ExternalInterface.available)
                ExternalInterface.call(jsfunc);

            _lightsLowered = true;
        }

        public function raiseLights():void
        {
            if (_lightsLowered == false)
                return;

var jsfunc:String = (<![CDATA[
function() {
    var backdropId = 'flixn_fmp_player_backdrop';
    document.body.removeChild(document.getElementById(backdropId));
}
]]>).toString();

            if (ExternalInterface.available)
                ExternalInterface.call(jsfunc);

            _lightsLowered = false;
        }
    }
}