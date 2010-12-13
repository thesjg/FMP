package com.flixn.player.controller
{
    import com.flixn.component.services.PlayerServices;
    import com.flixn.component.services.ServicesEvent;
    import com.flixn.component.statistics.PlayerStatistics;
    import com.flixn.component.video.FlixnVideoDisplay;
    import com.flixn.player.model.Settings;
    import com.flixn.player.view.ControlPanel;
    import com.flixn.player.view.MiniScrubber;
    import com.flixn.player.view.Menu;

    import flash.events.ProgressEvent;
    import flash.events.MouseEvent;
    import flash.events.TimerEvent;
    import flash.utils.Timer;

    import mx.core.Application;
    import mx.events.VideoEvent;
    import mx.events.MetadataEvent;

    public class Panel
    {
        private var _app:Object;
        private var _vcView:Object;
        private var _cpView:Object;
        private var _msView:MiniScrubber;
        private var _fvd:FlixnVideoDisplay;
        private var _mView:com.flixn.player.view.Menu;
        private var _stats:Object;
        private var _services:Object;
        private var _settings:Settings;

        private var _seekable:Boolean = false;
        private var _playing:Boolean = false;

        private var _source:String;

        private var _cpTimer:Timer;

        private var _lockedOpen:Boolean = true;

        private var _mediaProcessingInterval:int = 30;
        private var _mediaProcessingCount:int;
        private var _mediaProcessingTimer:Timer;

        public function Panel(app:Object, vc:Object, cpview:ControlPanel, msview:MiniScrubber, fvd:FlixnVideoDisplay, menu:com.flixn.player.view.Menu):void
        {
            _app = app;
            _vcView = vc;
            _cpView = cpview;
            _msView = msview;
            _fvd = fvd;
            _mView = menu;

            _stats = PlayerStatistics.getInstance();
            _services = PlayerServices.getInstance();
            _settings = new Settings();

            _services.addEventListener(ServicesEvent.MEDIA, onMediaLoaded);

            if (_settings.mediaId != '') {
                _services.playGetMedia(_settings.mediaId);
            }

            _fvd.addEventListener(VideoEvent.PLAYHEAD_UPDATE, onVideoPlayheadUpdate);
            _fvd.addEventListener(VideoEvent.COMPLETE, onVideoComplete);
            _fvd.addEventListener(VideoEvent.STATE_CHANGE, onVideoStateChange);
            _fvd.addEventListener(MetadataEvent.METADATA_RECEIVED, onMetadataReceived);

            _cpTimer = new Timer(_settings.panelTimeout, 1);
            _cpTimer.addEventListener('timer', _mouseOverTimer);

            _mediaProcessingTimer = new Timer(1000, _mediaProcessingInterval);
            _mediaProcessingTimer.addEventListener('timer', mediaProcessing);
        }

        public function playButtonClick():void
        {
            trace('Panel: Play button clicked');

            if (_source != '')
                playMedia();

            _playing = true;
        }

        public function pauseButtonClick():void
        {
            trace('Panel: Pause button clicked');

            _fvd.pause();
            _cpView.currentState = 'Paused';
            _playing = false;
        }

        public function tagButtonClick():void
        {

        }

        public function commentButtonClick():void
        {

        }

        public function menuButtonClick():void
        {
            if (_mView.visible == false) {
                if (_vcView.visible == true)
                    volumeButtonClick();

                _mView.visible = true;
                lockOpen();
            } else {
                _mView.visible = false;
                _mView.menu.hidePanels();
                freeOpen();
            }
        }

        public function volumeButtonClick():void
        {
            if (_vcView.visible == false) {
                _vcView.visible = true;
            } else {
                _vcView.visible = false;
            }

            _cpTimer.reset();
            _cpTimer.start();
        }

        public function seek(seekOffset:Number, scrubWidth:Number):void
        {
            if (_seekable == false)
                return;

            var seekTo:Number = (seekOffset / scrubWidth) * _fvd.totalTime;
            trace('Panel: Seeking to: ' + seekTo);
            _fvd.playheadTime = seekTo;
        }

        private function playMedia():void
        {
            _fvd.play();
            _cpView.currentState = 'Playing';

            _app.button_play_giant.visible = false;
            _seekable = true;

            if (_mView.visible == false)
                freeOpen();
        }

        private function mediaProcessing(ev:TimerEvent):void
        {
            _cpView.info.text = 'Media processing, retrying in ' + _mediaProcessingCount + ' seconds';
            _mediaProcessingCount--;

            if (_mediaProcessingCount < 1) {
                _cpView.info.text = 'Retrying media';
                _services.playGetMedia(_settings.mediaId);
            }
        }

        private function onMediaLoaded(ev:ServicesEvent):void
        {
            var result:XML = new XML(ev.result);
            var defaultAsset:XML = null;

            trace(result.toString());

            var status:String = result.status.toString();
            if ((status == 'MODERATION')) {
                _app.button_play_giant.visible = false;
                _app.button_warn_giant.visible = true;
                _cpView.button_play.enabled = false;
                _cpView.tag_container.width = 0;
                _cpView.comment_container.width = 0;
                _cpView.menu_container.width = 0;
                _cpView.volume_container.width = 0;
                _cpView.info.text = 'Media is undergoing moderation';

                /* TODO: Check for flixn.com ? ? ??? */
                if (_settings.moderationOverride == false)
                    return;
            }

            if (status == 'TRANSCODE') {
                _app.button_play_giant.visible = false;
                _app.button_warn_giant.visible = true;
                _cpView.button_play.enabled = false;
                _cpView.info.text = 'Media is currently processing';

                _mediaProcessingCount = _mediaProcessingInterval;
                _mediaProcessingTimer.reset();
                _mediaProcessingTimer.start();

                return;
            }

            for each (var asset:XML in result.assets.*) {
                trace('Target: ' + asset.target.toString());
                if (_settings.mediaTarget != '' && _settings.mediaTarget == asset.target.toString()) {
                    trace(asset.toString());
                    defaultAsset = asset;
                } else {
                    if (defaultAsset == null) {
                        trace(asset.toString());
                        defaultAsset = asset;
                    }
                }
            }

            if (defaultAsset == null) {
                _app.button_play_giant.visible = false;
                _app.button_warn_giant.visible = true;
                _cpView.button_play.enabled = false;
                _cpView.info.text = 'Unable to load media';
                return;
            }

            _app.button_warn_giant.visible = false;
            _app.button_play_giant.visible = true;
            _cpView.button_play.enabled = true;

            _source = result.protocol
                    + '://'
                    + result.locations.location.hostname
                    + defaultAsset.path;

            trace('Panel: Source file: ' + _source);

            _fvd.source = _source;
            _fvd.load();

            if (_playing == true)
                playMedia();
        }

        private function onVideoPlayheadUpdate(ev:VideoEvent):void
        {
            _cpView.progress.setProgress(ev.playheadTime, _fvd.totalTime);
            _msView.progress.setProgress(ev.playheadTime, _fvd.totalTime);

            _cpView.info.text = formatHMS(_fvd.playheadTime) + ' / ' + formatHMS(_fvd.totalTime);
        }

        private function onMetadataReceived(ev:MetadataEvent):void
        {
            _cpView.info.text = formatHMS(_fvd.playheadTime) + ' / ' + formatHMS(_fvd.totalTime);
        }

        private function formatHMS(source_seconds:Number):String
        {
            var seperator:String = ':';
            var hours:int = 0;
            var minutes:int = 0;
            var seconds:int = 0;

            var hours_out:String;
            var minutes_out:String;
            var seconds_out:String;

            while (source_seconds >= 3600) {
                hours++;
                source_seconds -= 3600;
            }

            while (source_seconds >= 60) {
                minutes++;
                source_seconds -= 60;
            }

            seconds = source_seconds;

            if (hours < 10 && hours > -1)
                hours_out = '0' + hours.toString();
            else
                hours_out = hours.toString();

            if (minutes < 10 && minutes > -1)
                minutes_out = '0' + minutes.toString();
            else
                minutes_out = minutes.toString();

            if (seconds < 10 && seconds > -1)
                seconds_out = '0' + seconds.toString();
            else
                seconds_out = seconds.toString();

            return hours_out + seperator + minutes_out + seperator + seconds_out;
        }

        private function onVideoComplete(ev:VideoEvent):void
        {
            _fvd.playheadTime = 0;
            _cpView.currentState = 'Paused';
            _playing = false;

            _cpView.info.text = '00:00:00 / ' + formatHMS(_fvd.totalTime);
        }

        private function onVideoStateChange(ev:VideoEvent):void
        {
            trace('xxx - state: ' + _fvd.state);
        }

        public function appMouseEvent(ev:MouseEvent):void
        {
            if (_lockedOpen == true)
                return;

            _cpTimer.reset();
            _cpTimer.start();

            if (_cpView.visible == false) {
                _showPanel();
            }
        }

        private function _mouseOverTimer(event:TimerEvent):void {
            _hidePanel();
        }

        private function _showPanel():void
        {
            _msView.progress.visible = false;
            _msView.visible = false;
            _cpView.visible = true;
        }

        private function _hidePanel():void
        {
            if (_lockedOpen == true)
                return;

            _cpTimer.stop();

            _cpView.visible = false;
            _msView.visible = true;
            _msView.progress.visible = true;
            _vcView.visible = false;
        }

        public function lockOpen():void
        {
            _lockedOpen = true;
            _cpTimer.stop();
        }

        public function freeOpen():void
        {
            _lockedOpen = false;
            _cpTimer.reset();
            _cpTimer.start();
        }
    }
}