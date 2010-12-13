package com.flixn.uploader.controller {

    import com.flixn.component.browserapi.BrowserAPI;
    import com.flixn.uploader.controller.Statistics;
    import com.flixn.uploader.model.Settings;
    import com.flixn.uploader.model.Upload;
    import com.flixn.uploader.view.ControlPanel;

    import flash.errors.IllegalOperationError;
    import flash.events.DataEvent;
    import flash.events.Event;
    import flash.events.EventDispatcher;
    import flash.events.ProgressEvent;
    import flash.net.FileReference;
    import flash.net.FileReferenceList;

    import mx.collections.ArrayCollection;
    import mx.controls.Button;
    import mx.controls.DataGrid;
    import mx.controls.TextInput;

    public class Uploader extends EventDispatcher
    {
        private var _settings:Settings;

        private var _refFile:Object;
        private var _fileList:ArrayCollection;
        private var _upload:Upload;
        private var _cpView:ControlPanel;
        private var _fileView:DataGrid;
        private var _statistics:Statistics;
        private var _refSelectFiles:FileReferenceList;

        private static var callbacksSet:Boolean = false;
        private static var mediaIds:Array;

        private var _browserAPI:BrowserAPI;

        public function Uploader(app:Object, settings:Settings)
        {
            _settings = settings;
            _fileList = new ArrayCollection();
            _upload = new Upload(app);
            _statistics = new Statistics();
            _browserAPI = new BrowserAPI();

            if (_settings.componentStyle == 'multiple') {
                FileReference.prototype.valueOf = function():String {
                    return this.creationDate + this.modificationDate + this.name + this.size + this.type;
                }

                _cpView = app.multiple_container.control_panel;
                _fileView = app.multiple_container.files;
            } else {
                _cpView = app.single_container.control_panel;
            }
        }

        public function browse():void
        {
            if (_settings.componentStyle == 'single') {
                _refFile = new FileReference();
            } else {
                _refFile = new FileReferenceList();
            }

            _refFile.addEventListener(Event.SELECT, onFileSelection);

            try {
                _refFile.browse();
            } catch (ex:IllegalOperationError) {

            } catch (ex:ArgumentError) {

            }
        }

        public function removeFile():void
        {
            if (_settings.componentStyle == 'single') {
                return;
            }

            for each (var i:uint in _fileView.selectedIndices.reverse())
                _fileList.removeItemAt(i);

            /* If the array is empty, return to start state */
            if (_fileList.length < 1) {
                _cpView.panel_main.currentState = 'startMultiple';
            }
        }

        public function send():void
        {
            if (callbacksSet == false) {
                _upload.setProgressCallback(this.onFileProgress);
                _upload.setCompleteCallback(this.onUploadComplete);
                _upload.setCompleteDataCallback(this.onUploadCompleteData);
                callbacksSet = true;
            }

            mediaIds = new Array();

            if (_settings.componentStyle == 'single') {
                _upload.setFileReference(FileReference(_refFile));
                _upload.uploadFile();
                dispatchEvent(new UploaderEvent(UploaderEvent.FILE_UPLOAD_STARTED, false, false));
            } else {
                var fRef:FileReference = _fileList[0].file;
                _upload.setFileReference(fRef);
                _upload.uploadFile();
                dispatchEvent(new UploaderEvent(UploaderEvent.FILE_UPLOAD_STARTED, false, false));
            }

            //_cpView.currentState = 'Status';
        }

        public function cancel():void
        {
            _upload.cancelUpload();

            if (_settings.componentStyle == 'multiple') {
                _fileList = new ArrayCollection();
                _fileView.dataProvider  = _fileList;
            }

            _cpView.panel.enterStartState();
        }

        private function onFileSelection(ev:Event):void
        {
            /* XXX: Check max file sizes */

            var fileRef:FileReference;

            if (_settings.componentStyle == 'single') {
                fileRef = FileReference(ev.target);

                trace(fileRef);

                _fileList.addItem({
                    name: fileRef.name,
                    size: _statistics.formatSize(fileRef.size),
                    file: fileRef
                });

                dispatchEvent(new UploaderEvent(UploaderEvent.FILE_LOADED, false, false, fileRef.name));

            } else {
                /* Make sure we don't add a file to the upload list twice */
                for (var i:int = 0; i < _fileList.length; i++) {
                    for (var j:int = 0; j < _refFile.fileList.length; j++) {
                        if (_fileList[i].file.valueOf() == _refFile.fileList[j].valueOf()) {
                            _refFile.fileList.splice(j, 1);
                            j--;
                        }
                    }
                }

                for each (fileRef in _refFile.fileList) {
                    _fileList.addItem({
                        name: fileRef.name,
                        size: _statistics.formatSize(fileRef.size),
                        file: fileRef
                    });
                }

                _fileView.dataProvider  = _fileList;
                _fileView.selectedIndex = _fileList.length - 1;

                if (_fileList.length > 0)
                    dispatchEvent(new UploaderEvent(UploaderEvent.FILE_LOADED, false, false, fileRef.name));
            }
        }

        private function onFileProgress(ev:ProgressEvent):void
        {
            dispatchEvent(ev);
        }

        private function onUploadComplete(ev:Event):void
        {
            trace('Upload Complete');

            if (_settings.componentStyle == 'multiple') {
                _fileList.removeItemAt(0);
                if (_fileList.length > 0) {
                    send();
                }
            }

            // XXX: Set a timer here, if onUploadCompleteData hasn't run by the time it expires, bail out
        }

        private function onUploadCompleteData(ev:DataEvent):void
        {
            trace('Upload Complete Data');
            trace(ev.data);

            var ret:XML = new XML('<result></result>');
            ret.appendChild(ev.data);

            if (ret.error.toString() != '') {
                _cpView.info.text = ret.error.toString();
            } else {
                dispatchEvent(new UploaderEvent(UploaderEvent.FILE_UPLOAD_COMPLETE, false, false, ret));

                var mediaId:String = ret.mediaid.toString();
                mediaIds.push(mediaId);

                _browserAPI.uploadCompleteFile(mediaId);

                if ((_settings.componentStyle == 'multiple' && _fileList.length < 1) ||
                     _settings.componentStyle == 'single') {

                    _browserAPI.uploadComplete(mediaIds);
                }
            }
        }
    }
}