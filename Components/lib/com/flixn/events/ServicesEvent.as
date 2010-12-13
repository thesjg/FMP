package com.flixn.events {

    import flash.events.*;

    public class ServicesEvent extends Event {

        public static const INIT:String   = 'init';
        public static const TICKET:String = 'ticket';
        public static const LIMITS:String = 'limits';

        public static const MEDIA:String  = 'media';
        public static const MEDIA_METADATA:String = 'media_metadata';

        public var result:Object;

        public function ServicesEvent(type:String,
                                      bubbles:Boolean = false,
                                      cancelable:Boolean = false,
                                      result:Object = null)
        {
            super(type, bubbles, cancelable);

            this.result = result;
        }

        public override function clone():Event {
            return new ServicesEvent(type, bubbles, cancelable, result);
        }

        public override function toString():String {
            return formatToString('ServicesEvent', 'type', 'bubbles',
                                  'cancelable', 'eventPhase', 'result');
        }
    }
}