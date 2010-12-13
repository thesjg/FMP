package com.flixn.recorder.model {

    import com.flixn.settings.SettingsCore;

	import com.flixn.interfaces.ISettingsPublishAudioVideo;
	import com.flixn.interfaces.ISettingsPublish;
	import com.flixn.interfaces.ISettingsVideo;
	import com.flixn.interfaces.ISettingsAudio;

    import mx.core.Application;
    import mx.rpc.events.ResultEvent;

    public class Settings extends SettingsCore implements ISettingsPublishAudioVideo, 
														  ISettingsPublish,
														  ISettingsAudio,
														  ISettingsVideo
    {
        private static var _setupDefaults:Boolean = false;

        private static var _videoEnable:Boolean;
        private static var _videoQuality:Number;
        private static var _videoWidth:Number;
        private static var _videoHeight:Number;
        private static var _videoFrameRate:Number;
        private static var _videoKeyFrameInterval:Number; // In frames

        private static var _audioEnable:Boolean;
        private static var _audioRate:Number; // In kHz
        private static var _audioUseEchoSuppression:Boolean;

		private static var _publishHostname:String;
		private static var _publishApplicationName:String;
		private static var _publishAppInstanceName:String;
		private static var _publishStreamName:String;
		private static var _publishType:String;

		private static var _recordHighQuality:Boolean;
        private static var _recordDuration:Number; // In seconds
		private static var _preRecordDuration:Number; // in seconds

        public function Settings(root:Application = null):void
        {
            if (!_setupDefaults) {
                super(root);
                addEventListener(ResultEvent.RESULT, settingsFileResult);

                _setupDefaults = true;

                _videoEnable = retrieveBooleanFlashVar('videoEnable', true);
                _videoQuality = retrieveNumericFlashVar('videoQuality', 95);
                _videoWidth = retrieveNumericFlashVar('videoWidth', 320);
                _videoHeight = retrieveNumericFlashVar('videoHeight', 240);
                _videoFrameRate = retrieveNumericFlashVar('videoFrameRate', 12);
                _videoKeyFrameInterval = retrieveNumericFlashVar('videoKeyFrameInterval', 36);
                
				_audioEnable = retrieveBooleanFlashVar('audioEnable', true);
                _audioRate = retrieveNumericFlashVar('audioRate', 22);
                _audioUseEchoSuppression = retrieveBooleanFlashVar('audioUseEchoSuppression', true);

				_publishHostname = retrieveStringFlashVar('publishHostname', 'localhost');
				_publishApplicationName = retrieveStringFlashVar('publishApplicationName', 'fxrecord');
				_publishAppInstanceName = retrieveStringFlashVar('publishAppInstanceName', null);
				_publishStreamName = retrieveStringFlashVar('publishStreamName', null);
				_publishType = retrieveStringFlashVar('publishType', 'record');
            
				_recordHighQuality = retrieveBooleanFlashVar('recordHighQuality', true);
				_recordDuration = retrieveNumericFlashVar('recordDuration', 300);
				_preRecordDuration = retrieveNumericFlashVar('preRecordDuration', 3);
			}
        }

        public function get videoEnable():Boolean
        {
            return _videoEnable;
        }

        public function get videoQuality():Number
        {
            return _videoQuality;
        }

        public function get videoWidth():Number
        {
            return _videoWidth;
        }

        public function get videoHeight():Number
        {
            return _videoHeight;
        }

        public function get videoFrameRate():Number
        {
            return _videoFrameRate;
        }

        public function get videoKeyFrameInterval():Number
        {
            return _videoKeyFrameInterval;
        }

        public function get audioEnable():Boolean
        {
            return _audioEnable;
        }

        public function get audioRate():Number
        {
            return _audioRate;
        }

        public function get audioUseEchoSuppression():Boolean
        {
            return _audioUseEchoSuppression;
        }

		public function get publishEndpoint():String
		{
			if (_publishAppInstanceName == null)
				return _publishHostname + '/' + _publishApplicationName;
			else
				return _publishHostname + '/' + _publishApplicationName + '/' + _publishAppInstanceName;
		}
		
		public function get publishAppInstanceName():String
		{
			return _publishAppInstanceName;
		}
		
		public function set publishStreamName(name:String):void
		{
			_publishStreamName = name;
		}
		
		public function get publishStreamName():String
		{
			return _publishStreamName;
		}

		public function get publishType():String
		{
			return _publishType;
		}
		
		public function get recordHighQuality():Boolean
		{
			return _recordHighQuality;
		}
		
        public function get recordDuration():Number
        {
            return _recordDuration;
        }

        public function set recordDuration(duration:Number):void
        {
            _recordDuration = duration;
        }
		
		public function get preRecordDuration():Number
		{
			return _preRecordDuration;
		}

        private function settingsFileResult(event:ResultEvent):void
        {
            if (event.result.settings.style == 'audio')
                _videoEnable = false;
        }
    }
}