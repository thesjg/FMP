package com.flixn.recorder.controller {

	import com.flixn.media.FXCamera;
	import com.flixn.media.FXMicrophone;
	import com.flixn.media.Recorder;
	import com.flixn.events.FXCameraEvent;
	import com.flixn.events.PublisherEvent;

	import com.flixn.interfaces.ISettingsVideo;
	import com.flixn.interfaces.ISettingsAudio;
	
    import com.flixn.statistics.RecorderStatistics;
    import com.flixn.recorder.view.ControlPanel;
	
	import flash.events.TimerEvent;
	import flash.events.NetStatusEvent;
	import flash.utils.Timer;

	import mx.formatters.DateFormatter;
	import mx.events.FlexEvent;

    public class Panel {

		public const TIMER_INTERVAL:int = 1000;

		[Bindable]
		private var camera:FXCamera;
		[Bindable]
		private var microphone:FXMicrophone;

		private var pub:Recorder = null;

        private var _cpView:ControlPanel;
		
		private var _preRecordTimer:Timer;
		private var _recordingTimer:Timer;
		
		private var _flushTimer:Timer;
		private var _flushCount:Number;

        public function Panel(view:ControlPanel):void
        {
            _cpView = view;
			setup();
		}
		
		public function setup():void
		{
			camera = new FXCamera(_cpView.settings);
			if (camera.open()) {
				microphone = new FXMicrophone(_cpView.settings);
				microphone.open();
			}

			camera.setup();
			_cpView.videoView.display.attachCamera(camera.camera);

            _cpView.currentState = 'recorderStart';
        }

        public function recordButtonClick():void
        {
			if (pub == null) {
				pub = new Recorder(_cpView.settings);
				pub.camera = camera;
				pub.microphone = microphone;
				pub.start();
				pub.network.addEventListener(NetStatusEvent.NET_STATUS, netStatusHandler);
			} else {
				/* Recording for a >1st time */
				preRecordTimerStart();
			}

            _cpView.currentState = 'recorderRecording';
            _cpView.controlPanelRecording.stopButton.visible = false;
			
            RecorderStatistics.addRecord();
        }

        public function stopButtonClick():void
        {
            pub.stop();
			_cpView.playback.clear();

			if (_cpView.settings.recordHighQuality) {
				_flushCount = pub.netStream.bufferLength;
				_cpView.currentState = 'recorderPublishing';
				_cpView.controlPanelPublishing.progress.minimum = 0;
				_cpView.controlPanelPublishing.progress.maximum = _flushCount;
				_flushTimer = new Timer(TIMER_INTERVAL / 10);
				_flushTimer.addEventListener(TimerEvent.TIMER, flushTimerUpdate);
				_flushTimer.start();
			} else {
				_cpView.currentState = 'recorderReview';
			}
        }

        public function saveButtonClick():void
        {
            pub.doSave();
            
            _revertToStartState();
			
			RecorderStatistics.addRecordComplete();
        }

        public function reviewButtonClick():void
        {
/*
            _cpView.currentState = 'recorderReview';
            _cpView.playback.startPlayback();
*/
            RecorderStatistics.addRecordView();
        }

        public function tryAgainButtonClick():void
        {
            if (_cpView.playback.playing == true)
                _cpView.playback.stopPlayback();

            _revertToStartState();
        }
		
		public function cancelButtonClick():void
		{
			
		}

        private function _revertToStartState():void
        {
            _cpView.controlPanelRecording.recordTime.text = '';
			_cpView.playback.clear(true);
			setup();
        }
		
		private function netStatusHandler(event:NetStatusEvent):void
		{
            switch (event.info.code) {
                case 'NetConnection.Connect.Success':
					preRecordTimerStart();
                    break;
                case 'NetConnection.Connect.Failed':
					_cpView.controlPanelRecording.recordTime.text = 'Connection failed.';
                	break;
                case 'NetConnection.Connect.Closed':
                	break;
                case 'NetConnection.Connect.Rejected':
					_cpView.controlPanelRecording.recordTime.text = 'Connection rejected.';
                    break;
                case 'NetStream.Record.Start':
					recordingTimerStart();
					_cpView.controlPanelRecording.stopButton.visible = true;
                    break;
                case 'NetStream.Record.Stop':
					recordingTimerComplete(null);
                    break;
				case 'NetStream.Buffer.Empty':
					break;
				case 'NetStream.Unpublish.Success':
					_cpView.playback.preparePlayback();
					break;
            }
		}
		
		private function flushTimerUpdate(event:TimerEvent):void
		{
			if (pub.netStream.bufferLength == 0) {
				_flushTimer.stop();
				_cpView.currentState = 'recorderReview';
				_cpView.playback.preparePlayback();
				return;
			}

			_cpView.controlPanelPublishing.progress.setProgress(pub.netStream.bufferLength, _flushCount);
		}
		
		private function preRecordTimerStart():void
		{
			_preRecordTimer = new Timer(TIMER_INTERVAL, _cpView.settings.preRecordDuration);
			_preRecordTimer.addEventListener(TimerEvent.TIMER, preRecordTimerUpdate);
			_preRecordTimer.addEventListener(TimerEvent.TIMER_COMPLETE, preRecordTimerComplete);
			_preRecordTimer.start();
		}
		
		private function preRecordTimerUpdate(event:TimerEvent):void
		{
			var dateFormatter:DateFormatter = new DateFormatter();
			dateFormatter.formatString = 'Recording in NN:SS';

			var timeToRecord:Date = new Date((_cpView.settings.preRecordDuration * 1000) -
											 (_preRecordTimer.currentCount * 1000) || 100);

			_cpView.controlPanelRecording.recordTime.text = dateFormatter.format(timeToRecord);
		}
		
		private function preRecordTimerComplete(event:TimerEvent):void
		{
			_preRecordTimer.stop();
			pub.initiateRecording();
		}
		
		private function recordingTimerStart():void
		{
			var dateFormatter:DateFormatter = new DateFormatter();
			dateFormatter.formatString = 'NN:SS';
			
			var totalAlloted:Date = new Date(_cpView.settings.recordDuration * 1000);

			_cpView.controlPanelRecording.recordTime.text = '00:00 / ' + dateFormatter.format(totalAlloted);

			_recordingTimer = new Timer(TIMER_INTERVAL, _cpView.settings.recordDuration);
			_recordingTimer.addEventListener(TimerEvent.TIMER, recordingTimerUpdate);
			_recordingTimer.addEventListener(TimerEvent.TIMER_COMPLETE, recordingTimerComplete);
			_recordingTimer.start();
		}
		
		private function recordingTimerUpdate(event:TimerEvent):void
		{
			var dateFormatter:DateFormatter = new DateFormatter();
			dateFormatter.formatString = 'NN:SS';

			var timeRecorded:Date = new Date(_recordingTimer.currentCount * 1000 || 100);
			var totalAlloted:Date = new Date(_cpView.settings.recordDuration * 1000);

			_cpView.controlPanelRecording.recordTime.text = 
				dateFormatter.format(timeRecorded) + ' / ' + dateFormatter.format(totalAlloted);
		}
		
		private function recordingTimerComplete(event:TimerEvent):void
		{
			_recordingTimer.stop();
		}
    }
}