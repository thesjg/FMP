package com.flixn.events
{
	import flash.events.Event;

	public class FXCameraEvent extends Event 
	{
        public static const MODES_BEGIN:String    = 'modes_begin';
		public static const MODES_COMPLETE:String = 'modes_complete';
		
		public var result:Object;

		public function FXCameraEvent(type:String,
		                              bubbles:Boolean = false,
									  cancelable:Boolean = false,
									  result:Object = null)
		{ 
			super(type, bubbles, cancelable);
			
			this.result = result;
		} 
		
		public override function clone():Event 
		{ 
			return new FXCameraEvent(type, bubbles, cancelable, result);
		} 
		
        public override function toString():String {
            return formatToString('FXCameraEvent', 'type', 'bubbles',
                                  'cancelable', 'eventPhase', 'result');
        }
	}	
}