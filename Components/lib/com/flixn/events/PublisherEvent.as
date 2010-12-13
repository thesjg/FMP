package com.flixn.events 
{
	import flash.events.Event;
	
	/**
	 * ...
	 * @author DefaultUser (Tools -> Custom Arguments...)
	 */
	public class PublisherEvent extends Event 
	{
		
		public function PublisherEvent(type:String, bubbles:Boolean=false, cancelable:Boolean=false) 
		{ 
			super(type, bubbles, cancelable);
			
		} 
		
		public override function clone():Event 
		{ 
			return new PublisherEvent(type, bubbles, cancelable);
		} 
		
		public override function toString():String 
		{ 
			return formatToString("PublisherEvent", "type", "bubbles", "cancelable", "eventPhase"); 
		}
		
	}
	
}