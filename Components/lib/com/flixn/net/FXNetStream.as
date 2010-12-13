package com.flixn.net
{
	import com.flixn.media.FXCamera;
	import com.flixn.media.FXMicrophone;
	
	import flash.net.NetStream;
	import flash.events.Event;
	
	public class FXNetStream
	{
		private var _netStream:flash.net.NetStream;
		
		public function FXNetStream(connection:FXNetConnection)
		{
			_netStream = new flash.net.NetStream(connection);
		}
		
		public function get bufferLength():Number
		{
			return _netStream.bufferLength;
		}
		
		public function get bufferTime():Number
		{
			return _netStream.bufferTime;
		}
		
		public function set bufferTime(bufferTime:Number):void
		{
			_netStream.bufferTime = bufferTime;
		}
		
		public function attachAudio(microphone:FXMicrophone):void
		{
			_netStream.attachAudio((microphone != null) ? microphone.microphone : null);
		}
		
		public function attachCamera(camera:FXCamera, snapshotMilliseconds:int = -1):void
		{
			_netStream.attachCamera((camera != null) ? camera.camera : null, snapshotMilliseconds);
		}
		
		public function publish(name:String = null, type:String = null):void
		{
			_netStream.publish(name, type);
		}
		
		public function close():void
		{
			_netStream.close();
		}
		
		/* IEventDispatcher passthrough */
		
		public function addEventListener(type:String, listener:Function, useCapture:Boolean = false,
		                                 priority:int = 0, useWeakReference:Boolean = false):void
		{
			return _netStream.addEventListener(type, listener, useCapture, priority, useWeakReference);
		}
		
		public function dispatchEvent(event:Event):Boolean
		{
			return _netStream.dispatchEvent(event);
		}
		
		public function hasEventListener(type:String):Boolean
		{
			return _netStream.hasEventListener(type);
		}
		
		public function removeEventListener(type:String, listener:Function, useCapture:Boolean = false):void
		{
			return _netStream.removeEventListener(type, listener, useCapture);
		}

		public function willTrigger(type:String):Boolean
		{
			return _netStream.willTrigger(type);
		}
	}
}