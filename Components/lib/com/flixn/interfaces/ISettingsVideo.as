package com.flixn.interfaces 
{
	public interface ISettingsVideo
	{
		function get videoEnable():Boolean;
		function get videoQuality():Number;
		function get videoWidth():Number;
		function get videoHeight():Number;
		function get videoFrameRate():Number;
		function get videoKeyFrameInterval():Number;
	}
}