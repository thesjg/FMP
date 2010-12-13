package com.flixn.recorder.controller {

    import com.flixn.recorder.model.Settings;

    import mx.controls.VideoDisplay;

    public class Playback {

        [Bindable]
        public var playing:Boolean = false;

        private var _display:VideoDisplay = null;
		private var _controls:Object = null;
        private var _settings:Settings;

        public function Playback(display:VideoDisplay = null, controls:Object = null):void
        {
            trace('Initializing Playback class');

			/* XXX: Use FxVideoDisplay */
            if (display == null)
                _display = new VideoDisplay();
            else
                _display = display;
				
			_controls = controls;
				
            _settings = new Settings();
        }

        public function preparePlayback():void
        {
			if (playing == true)
				return;

            _display.autoPlay = false;
			_display.autoRewind = true;
			_display.source = 'rtmp://' + _settings.publishEndpoint + '/' + _settings.publishStreamName + '.flv';
			trace('Playback: preparing ' + _display.source);
            _display.load();
            _display.playheadTime = 0;
            _display.bufferTime = 3;
            playing = true;
        }

        public function startPlayback():void
        {
            _display.play();
        }

        public function stopPlayback():void
        {
            _display.stop();
            _display.source = null;
            playing = false;
        }
		
		public function clear(display:Boolean = false, controls:Boolean = false):void
		{
			_display.visible = display;
			_controls.visible = controls;
		}
    }
}