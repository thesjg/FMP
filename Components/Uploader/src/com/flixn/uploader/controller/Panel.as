package com.flixn.uploader.controller {

    import com.flixn.component.statistics.UploaderStatistics;
    import com.flixn.uploader.controller.UploaderEvent;
    import com.flixn.uploader.view.ControlPanel;

    import flash.events.ProgressEvent;

    public class Panel {

        private var _cpView:ControlPanel;
        private var _stats:Object;
        private var _statistics:Statistics;

        public function Panel(view:ControlPanel):void
        {
            _cpView = view;

            _stats = UploaderStatistics.getInstance();

            _cpView.uploader.addEventListener(UploaderEvent.FILE_UPLOAD_STARTED, onFileUploadStart);
            _cpView.uploader.addEventListener(UploaderEvent.FILE_UPLOAD_COMPLETE, onFileUploadComplete);

            enterStartState();
        }

        public function enterStartState():void
        {
            _cpView.currentState = 'uploaderStart';

            if (_cpView.settings.componentStyle == 'single')
                _cpView.panel_main.currentState = 'startSingle';
            else
                _cpView.panel_main.currentState = 'startMultiple';
        }

        public function browseButtonClick():void
        {
            _cpView.uploader.addEventListener(UploaderEvent.FILE_LOADED, onFileLoaded);
            _cpView.uploader.browse();
        }

        public function uploadButtonClick():void
        {
            if (_cpView.settings.componentStyle == 'single')
                _cpView.panel_main.textinput_filename.text = '';

            _cpView.currentState = 'uploaderUploading';
            _cpView.uploader.send();
            _cpView.uploader.addEventListener(ProgressEvent.PROGRESS, onFileProgress);
        }

        public function removeFileButtonClick():void
        {
            _cpView.uploader.removeFile();
        }

        public function stopButtonClick():void
        {
            _cpView.uploader.cancel();
        }

        private function onFileLoaded(ev:UploaderEvent):void
        {
            if (_cpView.settings.componentStyle == 'single') {
                _cpView.panel_main.currentState = 'uploadSingle';
                _cpView.panel_main.textinput_filename.text = String(ev.result);
            } else {
                _cpView.panel_main.currentState = 'uploadMultiple';
            }
        }

        private function onFileProgress(ev:ProgressEvent):void
        {
            _cpView.panel_uploading.progress.setProgress(ev.bytesLoaded, ev.bytesTotal);
            _cpView.info.text = _statistics.getStatsString(ev);
        }

        private function onFileUploadStart(ev:UploaderEvent):void
        {
            _statistics = new Statistics();
        }

        private function onFileUploadComplete(ev:UploaderEvent):void
        {
            _cpView.info.text = 'Upload complete';
        }
    }
}