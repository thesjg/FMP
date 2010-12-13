package com.flixn.interfaces 
{
	public interface ISettingsAudioVideo
	{
		function get audioEnable():Boolean;
		function get audioRate():Number;
		function get audioUseEchoSuppression():Boolean;

		function get videoEnable():Boolean;
		function get videoQuality():Number;
		function get videoWidth():Number;
		function get videoHeight():Number;
		function get videoFrameRate():Number;
		function get videoKeyFrameInterval():Number;
	}
}