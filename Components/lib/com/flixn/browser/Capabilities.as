package com.flixn.browser {

    import flash.system.Capabilities;

    public class Capabilities
    {
        public function Capabilities():void
        {
        }

        public static function getCapabilities():String
        {
            return flash.system.Capabilities.serverString;
        }

        public static function getCameras():Array
        {
            return Camera.names;
        }
	
        public static function getMicrophones():Array
        {
            return Microphone.names;
        }
    }
}