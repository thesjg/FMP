package com.flixn.interfaces 
{
	public interface ISettingsPublishAudioVideo
	{
		function get publishEndpoint():String;
		function get publishAppInstanceName():String;
		function get publishStreamName():String;
		function set publishStreamName(name:String):void;
		function get publishType():String;

		function get audioEnable():Boolean;
		function get audioRate():Number;
		function get audioUseEchoSuppression():Boolean;

		function get videoEnable():Boolean;
		function get videoQuality():Number;
		function get videoWidth():Number;
		function get videoHeight():Number;
		function get videoFrameRate():Number;
		function get videoKeyFrameInterval():Number;


		function get recordHighQuality():Boolean;
		function get recordDuration():Number;
	}
}