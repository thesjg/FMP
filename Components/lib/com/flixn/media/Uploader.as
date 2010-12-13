package com.flixn.media 
{
    import mx.events.*;
    import mx.rpc.events.*;
    import flash.events.*;
    import flash.net.*;

    import com.flixn.uploader.model.*;
    import com.flixn.component.browserapi.BrowserAPI;

    public class Uploader
    {
        private var _refFile:FileReference;
        private var _refUpload:URLRequest;
        private var _cbProgress:Function;
        private var _cbComplete:Function;
        private var _cbCompleteData:Function;

        private var _services:Services;
        private var _browserAPI:BrowserAPI;

        public function Upload(app:Object):void {
            _cbProgress = null;
            _cbComplete = null;
            _cbCompleteData = null;

            _services = new Services(app);
            _browserAPI = new BrowserAPI();
        }

        public function setFileReference(fileReference:FileReference):void {
            _refFile = fileReference;
        }

        public function getFileReference():FileReference {
            return _refFile;
        }

        public function setProgressCallback(callback:Function):void {
            _cbProgress = callback;
        }

        public function setCompleteCallback(callback:Function):void {
            _cbComplete = callback;
        }

        public function setCompleteDataCallback(callback:Function):void {
            _cbCompleteData = callback;
        }

        public function getMaxFileSize():Number {
            return 100000;
        }

        public function getMaxUploadSize():Number {
            return 100000;
        }

        public function uploadFile():void {
            /* XXX: Get upload endpoint via services */

            //_services.sessionInitiate();

            _services.addEventListener(ServicesEvent.TICKET, uploadFileHaveTicket);
            _services.uploadGetTicket();
        }

        private function uploadFileHaveTicket(event:ServicesEvent):void {
trace(event.result);

            if (event.result.error) {
                trace('error: ' + event.result.error);
                // raise event? notify controller?
                return;
            }

            trace('ticketId: ' + event.result.uploadticket.ticketId);
            trace('endpoint: ' + event.result.uploadticket.endpoint);

            _refUpload = new URLRequest(event.result.uploadticket.endpoint +
                                        '/' +
                                        event.result.uploadticket.ticketId);

            var params:URLVariables = new URLVariables();
            params.creation_url = _browserAPI.getURL();
            _refUpload.method = URLRequestMethod.POST;
            _refUpload.data = params;

            if (_cbProgress !== null)
                _refFile.addEventListener(ProgressEvent.PROGRESS, _cbProgress);

            if (_cbComplete !== null)
                _refFile.addEventListener(Event.COMPLETE, _cbComplete);

            if (_cbCompleteData !== null)
                _refFile.addEventListener(DataEvent.UPLOAD_COMPLETE_DATA, _cbCompleteData);

            _refFile.upload(_refUpload);
        }

        public function cancelUpload():void {
            _refFile.cancel();
            _refFile = null;
        }
    }
}