package com.flixn.interfaces 
{
	public interface ISettingsPublish
	{
		function get publishEndpoint():String;
		function get publishAppInstanceName():String;
		function get publishStreamName():String;
		function set publishStreamName(name:String):void;
		function get publishType():String;
	}
}