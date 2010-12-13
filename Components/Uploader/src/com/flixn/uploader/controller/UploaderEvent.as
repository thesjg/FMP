package com.flixn.uploader.controller {

    import flash.events.*;

    public class UploaderEvent extends Event {

        public static const FILE_LOADED:String          = 'fileLoaded';
        public static const FILE_UPLOAD_STARTED:String  = 'fileUploadStarted';
        public static const FILE_UPLOAD_COMPLETE:String = 'fileUploadComplete';

        public var result:Object;

        public function UploaderEvent(type:String,
                                      bubbles:Boolean = false,
                                      cancelable:Boolean = false,
                                      result:Object = null)
        {
            super(type, bubbles, cancelable);

            this.result = result;
        }

        public override function clone():Event {
            return new UploaderEvent(type, bubbles, cancelable, result);
        }

        public override function toString():String {
            return formatToString('UploaderEvent', 'type', 'bubbles',
                                  'cancelable', 'eventPhase', 'result');
        }
    }
}