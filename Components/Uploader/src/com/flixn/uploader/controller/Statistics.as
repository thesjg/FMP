package com.flixn.uploader.controller {

    import flash.events.*;

    public class Statistics {

        public var sizeLabels:Array;
        public var sizeDivisor:Number;
        public var sizePrecision:Number;
        public var sizeFormat:String;

        protected var timeDivisors:Array;

        protected var timeStart:Date;

        public function Statistics() {
            sizeLabels  = new Array('KB', 'MB', 'GB', 'TB', 'PB', 'EB');
            sizeDivisor = 1024;

            timeDivisors = new Array(3600, 60, 1);

            timeStart = new Date();
        }

        public function formatSize(size:Number):String {
            sizePrecision = 1;
            sizeFormat = '$size $label';
            return doFormat(size);
        }

        public function formatSizeBW(size:Number):String {
            sizePrecision = 2;
            sizeFormat = '$size $label/s';
            return doFormat(size);
        }

        public function formatTime(interval:Number):String {
            var retInterval:Number = interval;
            var retString:String;
            var divisor:Number;

            var first:Boolean = true;
            for (var i:Number = 0; i < timeDivisors.length; i++) {

                divisor = timeDivisors[i];

                var tInt:Number = Math.floor(retInterval / divisor);

                if (tInt) {
                    if (tInt < 10 && !first)
                        retString += '0' + tInt;
                    else
                        retString += tInt;

                    if (i < timeDivisors.length-1)
                        retString += ':';

                    retInterval -= divisor * tInt;

                    first = false;
                }
            }

            return retString;
        }

        private function doFormat(size:Number):String {
            var retSize:Number = size;
            var retLabel:String;
            var retString:String;

            for each (retLabel in sizeLabels) {
                retSize /= sizeDivisor;

                if (retSize < sizeDivisor)
                    break;
            }

            retString = sizeFormat.replace('$size', retSize.toFixed(sizePrecision));
            retString = retString.replace('$label', retLabel);
            return retString;
        }

        public function setTimeStart():void {

        }

        public function getTimeElapsed():void {

        }

        /*
        */
        public function getStatsString( ev:ProgressEvent ):String
        {
            var timeNow:Date = new Date();
            var totalDuration:Number = (timeNow.time - timeStart.time) / 1000;

            trace('Seconds: ' + totalDuration);

//            var remainingFormatter = new DateFormat("m:s");

            var speedNum:Number = ev.bytesLoaded / totalDuration;

            var speed:String     = 'Speed: '      + formatSizeBW(speedNum) + ', ';
            //var loaded:String    = 'Uploaded: '   + formatSize(ev.bytesLoaded) + ' / ' + formatSize(ev.bytesTotal) + ", ";
            var percent:String   = 'Completion: ' + Math.round((ev.bytesLoaded/ev.bytesTotal)*100) + '%';
            //var remaining:String = 'Remaining: '  + formatTime((ev.bytesTotal - ev.bytesLoaded) / speedNum);
            return String( speed + percent );
        }
    }
}

/*
 * Upload Speed (Last n Seconds)    $uploadRate
 * Upload Speed                     $uploadRateTotal
 * Uploaded (Current File)          $uploaded
 * Total Size (Current File)        $toUpload
 * Uploaded (Overall)               $uploadedTotal
 * Total Size (Overall)             $toUploadTotal
 * Upload Size Limit (File)         $uploadLimit
 * Upload Size Limit (Overall)      $uploadLimitTotal
 * Number of Files Being Uploaded   $count
 * Max Number of Files              $countLimit
 * Currently Uploading File Number  $countCurrent
 * Completion Percentage (File)     $completion
 * Completion Percentage (Overall)  $completionTotal
 * Time Remaining H:M:S             $timeRemaining
 * Time Elapsed H:M:S               $timeElapsed
*/