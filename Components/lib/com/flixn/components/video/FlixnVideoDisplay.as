package com.flixn.components.video {

    import com.flixn.player.model.Settings;
    import mx.controls.VideoDisplay;



	import flash.display.Sprite;
	import flash.events.*;
	import flash.net.*;
	import flash.utils.Timer;
	import flash.utils.getTimer;
	
	import mx.controls.Alert;
	import mx.events.CuePointEvent;
	import mx.events.MetadataEvent;
	import mx.events.VideoEvent;
	import mx.rpc.events.FaultEvent;
	import mx.rpc.events.ResultEvent;
	import mx.rpc.http.mxml.HTTPService;




    public class FlixnVideoDisplay extends VideoDisplay
    {



        private var _settings:Settings;



	/**
	 *	Constants
	 */
	public static const LABEL_PLAY:String       = ">";//"Play";
	public static const TOOLTIP_PLAY:String     = "Play";
	public static const LABEL_PAUSE:String      = "| |";//"Pause";
	public static const TOOLTIP_PAUSE:String    = "Pause";
	public static const LABEL_STOP:String       = "Stop";
	public static const TOOLTIP_STOP:String     = "Stop";
	public static const LABEL_FORWARD:String    = ">>";
	public static const TOOLTIP_FORWARD:String  = "Forward";
	public static const LABEL_BACK:String       = "<<";
	public static const TOOLTIP_BACK:String     = "Back";

	public static const BUTTON_WIDTH:uint           = 40;
	public static const DURATION_BAR_MAX:Number     = 100;
	public static const DURATION_BAR_WIDTH:Number   = 300;
	
	public static const REWIND_BACK_INTERVAL:Number     = 1;    // seconds
	public static const REWIND_FORWARD_INTERVAL:Number  = 0.1;	// seconds
	public static const REW_REPEAT_TIMEOUT:Number       = 1200; // milliseconds
	
	
	
	
	/**
	 *	Instance members
	 */
	
	//TODO: find proper place for guaranteed calculation of this,
	//		because it's not always works in VideoEvent.READY handler..
	[Bindable]
    private var durationRatio:Number = 0;

	private var rewInterval:Number = 0;
	private var cancelBackRew:Boolean = false;
	//private var previousPHPosition : Number;	// previous playhead position
	private var previousTime:Number = 0;
	
	/**
	 * 	Seekpoints
	 */
	private var seekpoints:Array;
	private var targetSeekpoint:Number;

	public var download_progress:Sprite;
		
	//	TODO: create custom formatter, based on NumberFormatter
	//private var dateFormatter:DateFormatter = new DateFormatter();
	

	
	//	previous vars
	private var playlist:XMLList;
	private var playlistIdx:uint;
	private var videoLength:String;
	private var start:Date;
	private var seekTo:Number;
	
	[Bindable]
	private var videoFileTotalBytes:Number;

	[Bindable]
	public var videoName:String;


        public function FlixnVideoDisplay()
        {
            super();

            _settings = new Settings();

            autoPlay = _settings.autoPlay;
            bufferTime = _settings.bufferTime;

            maintainAspectRatio = true;

            playheadUpdateInterval = 100;

            onCreationComplete();
        }

	/**
	 * 	Methods
	 */
	private function stateLogger():void
	{

		//trace("-> "+state);
		
		//textIndicator.text = videoDisp.state;
		//textIndicator.visible = true;
		//textIndicator.validateNow();

	}
	
	private function onCreationComplete():void
	{
		/*
		//	all VideoEvent events
		VideoEvent.BUFFERING			x
		VideoEvent.CLOSE
		VideoEvent.COMPLETE				x
		VideoEvent.CONNECTION_ERROR		x
		VideoEvent.DISCONNECTED
		VideoEvent.EXEC_QUEUED_CMD
		VideoEvent.LOADING				x
		VideoEvent.PAUSED
		VideoEvent.PLAYHEAD_UPDATE		x
		VideoEvent.PLAYING
		VideoEvent.READY				x
		VideoEvent.RESIZING
		VideoEvent.REWIND				x
		VideoEvent.REWINDING			x
		VideoEvent.SEEKING				x
		VideoEvent.STATE_CHANGE			x
		VideoEvent.STOPPED				x
		*/
		
		addEventListener( VideoEvent.READY, handlerVideoReady );
		addEventListener( VideoEvent.PLAYHEAD_UPDATE, handlerVideoPlayheadUpdate );
		addEventListener( VideoEvent.COMPLETE, handlerVideoComplete );
		addEventListener( VideoEvent.REWIND, handlerVideoRewind);//	on auto-rewind
		addEventListener( VideoEvent.STATE_CHANGE, handlerVideoStateChange);

		
		//	never-called !!!
		addEventListener( VideoEvent.BUFFERING, handlerVideoBuffering );
		addEventListener( VideoEvent.LOADING, handlerVideoLoading );
		addEventListener( VideoEvent.STOPPED, handlerVideoStopped);
		addEventListener( VideoEvent.REWINDING, handlerVideoRewinding);
		addEventListener( VideoEvent.SEEKING, handlerVideoSeeking);//looks like it never enters SEEKING handler
		addEventListener( VideoEvent.CONNECTION_ERROR, handlerVideoConnectionError);// never called
		
		/*
		//	all VideoDisplay events
		close
		complete
		cuePoint
		metadataReceived
		playheadUpdate
		progress
		ready
		rewind
		stateChange
		*/

		addEventListener(CuePointEvent.CUE_POINT, onCuePoint); //looks like never called
		addEventListener(MetadataEvent.METADATA_RECEIVED, onMetadataReceived);
		addEventListener(ProgressEvent.PROGRESS, onProgress);


		stateLogger();
		
		//	aligning loading ProgressBar
		// loadingProgress.x = durationBar.x + 7;
		
		//dateFormatter.formatString = "NN:SS";
		/*
		download_progress = new Sprite();
		download_progress.graphics.lineStyle();
		//download_progress.graphics.beginFill(0x00FF00);
		download_progress.graphics.drawRect(0, -2.5, 100, 5);
		//durationBar.add
		addChild( download_progress );
		*/

		//loadPlaylist();
			
	}

	
	/***************************************************************************
	 *	VideoDisplay handlers
	 ***************************************************************************/
	private function onCuePoint( event:CuePointEvent ):void
	{
		trace("CUE_POINT");
	}
	
	private function onMetadataReceived( event:MetadataEvent ):void
	{
		trace("METADATA_RECEIVED");
		
		//	TODO:
		//	Done: fetch seek-points from metadata for H264
		if (metadata && metadata.seekpoints)
		{
			//	H264 format with seekpoints defined
			if (metadata.seekpoints.length > 0)
			{
				//	seekpoints[]
				trace("H264: seekpoints[].length = "+metadata.seekpoints.length);
				seekpoints = metadata.seekpoints;
				
				//	dump seekpoints
				//trace("H264: seekpoints[]: ");
				for (var i:int = 0; i < seekpoints.length; i++) {
					//trace( "time: "+seekpoints[i].time );
				}
				//trace();
				
			}
		}
		else
		{
			//	FLV format
			seekpoints = null;
		}
	}
	
	private function onProgress(event:ProgressEvent):void
	{
		trace("PROGRESS in downloading...");
	}
	
	/***************************************************************************
	 * 	End of:
	 *	VideoDisplay handlers
	 ***************************************************************************/

	
	/***************************************************************************
	 *  @private
	 *
	 *	VideoEvent handlers
	 ***************************************************************************/
	private function handlerVideoStateChange(event:VideoEvent):void
	{
		trace("STATE_CHANGE");
		stateLogger();
		
		//if (videoDisp.state == vid)
		if (event.state == VideoEvent.CONNECTION_ERROR) {
			//	to close stream
			close();
	        togglePlayPause( VideoEvent.PAUSED, true );// just to update UI
		}
		

	}

	private function handlerVideoConnectionError(event:VideoEvent):void
	{
		//stateLogger();
		trace("CONNECTION_ERROR");
		//videoDisp.close();
	}

	private function handlerVideoBuffering(event:VideoEvent):void
	{
		stateLogger();
		trace(this+".handlerVideoBuffering():");
	}

	private function handlerVideoLoading(event:VideoEvent):void
	{
		stateLogger();
		trace(this+".handlerVideoLoading():");
	}
	
	private function handlerVideoReady(event:VideoEvent):void
	{
		stateLogger();
		trace("Video: totalTime (sec) = "+totalTime);
		//this.totalTime = videoDisp.totalTime;
		if (totalTime > 0) {	//	not -1
			//this.durationRatio = DURATION_BAR_MAX / this.totalTime;
			this.durationRatio = DURATION_BAR_MAX / totalTime;
		}
	}
	
	private function handlerVideoComplete(event:VideoEvent):void
	{
		stateLogger();
		trace(""+this+" : video ended.");
		togglePlayPause( VideoEvent.PAUSED, true );//	to switch pause/play control
	}
	
	private function handlerVideoStopped(event:VideoEvent):void
	{
		stateLogger();
		trace(""+this+" : video stopped.");
		//togglePlayPause( VideoEvent.PAUSED, true );//	to switch pause/play control
	}
	
	private function handlerVideoRewind(event:VideoEvent):void
	{
		stateLogger();
		trace(""+this+" : video rewind.");
		//togglePlayPause( VideoEvent.PAUSED, true );//	to switch pause/play control
	}
	
	private function handlerVideoRewinding(event:VideoEvent):void
	{
		stateLogger();
		trace(""+this+" : video rewinding.");
		//togglePlayPause( VideoEvent.PAUSED, true );//	to switch pause/play control
	}
	
	private function handlerVideoSeeking(event:VideoEvent):void
	{
		//stateLogger();
		trace("SEEKING event - inside handler...");
	}


	private function handlerVideoPlayheadUpdate(event:VideoEvent):void
	{
		stateLogger();
		
		//trace("[Event type=\"playheadUpdate\"] state = "+event.state+"; stateResponsive = "+event.stateResponsive);
		
        //trace("Playhead position:  "+playheadTime + "  event.playheadTime = " + event.playheadTime + ' totalTime: ' + totalTime);
		
		//	this most likely not really needed - no need of removing REWIND_BACK listener
		if (hasEventListener("REWIND_BACK")) {
			removeEventListener("REWIND_BACK", handlerBackRewind);
			rewInterval = 0;
			cancelBackRew = true;
		}

		
		//this.durationBar.value = event.playheadTime / videoDisp.totalTime * DURATION_BAR_MAX;
		
        /*
		if (this.durationRatio == 0) {
			trace("WARNING: durationRatio == 0.  Recalculating...");
			this.durationRatio = DURATION_BAR_MAX / videoDisp.totalTime;
		}
		//	to make this frequent computation a bit lighter
		this.durationBar.value = videoDisp.playheadTime * this.durationRatio;// event.playheadTime
		*/
		
		//trace("durationBar.value = "+this.durationBar.value+" (of max = "+this.durationBar.maximum+" )");
		
        //this.durationBar.validateNow();

	}
	
	private function onTimer(event:TimerEvent):void
	{
		if (cancelBackRew) {
			trace("REWIND_BACK -- CANCELLED.");
			rewInterval = 0;
			return;
		}
			
		trace("TIMER: delay = "+ (flash.utils.getTimer() - previousTime));
		previousTime = flash.utils.getTimer();
		proceedBackRewind();
	}
	
	private function proceedBackRewind():void
	{
		/*
		if (cancelBackRew) {
			trace("REWIND_BACK -- CANCELLED.");
			return;
		}
		*/
		trace("REWIND_BACK");
		

		//private var rewInterval : Number = 0;
		rewInterval += REWIND_BACK_INTERVAL;//	first iteration

		if (rewInterval >= playheadTime) {
			//	to the video's beginning
			playheadTime = 0;
			return;
		}

		if (rewInterval < playheadTime) {
			
			if (cancelBackRew) {
				trace("REWIND_BACK -- CANCELLED.");
				rewInterval = 0;
				return;
			}
			
			trace("rewInterval = "+rewInterval);
			var newPoint:Number = playheadTime - rewInterval;
			trace("playheadTime FROM: "+playheadTime+"  TO: "+newPoint);
			//videoDisp.playheadTime = videoDisp.playheadTime - rewInterval;
			playheadTime = newPoint;
			//trace("Playhead after REW: "+videoDisp.playheadTime);
			//rewInterval += REWIND_BACK_INTERVAL; //incrementing back rew interval
			
			//previousPHPosition = videoDisp.playheadTime; //storing new playhead position
			
			var timeout:Timer = new Timer(REW_REPEAT_TIMEOUT, 1);//1200 //1000 //500 //750
			timeout.addEventListener(TimerEvent.TIMER, onTimer);
			timeout.start();
		}
	}
	
	/**
	 * 	Handler for custom REWIND_BACK event.
	 */
	private function handlerBackRewind( event:Event ):void
	{
		//stateLogger();
			
		previousTime = flash.utils.getTimer();
		proceedBackRewind();
		
	}
	

	private function rewindBack():void
	{
		//	TODO:
		//	Create separate logic for H264 and FLV formats.
		//
		//	some issue with second and ongoing "<<" or ">>" pressing - looks like playheadTime doesn't update..
		//
		//	1. Create seeking back loop till nearest keyframe is found, and playhaed's position really changed.
		
		trace("--------------------------------------------------------------------");
		trace("rewind  <<");
		trace("--------------------------------------------------------------------");
		//stateLogger();
		
		if (state == VideoEvent.DISCONNECTED)
		{
			return;
		}

		//	for H264 format
		if (seekpoints)
		{
			var newPoint : Number = playheadTime - REWIND_BACK_INTERVAL;
			trace("back FROM: "+playheadTime+"  TO: "+newPoint);
			if (newPoint < 0) {
				newPoint = 0;
			}
			playheadTime = newPoint;
			return;
		}
		
		//	for FLV format
		cancelBackRew = false;
		var type:String = "REWIND_BACK";
		addEventListener( type, handlerBackRewind );
		dispatchEvent( new Event(type) );
/*
		rewInterval += REWIND_BACK_INTERVAL;
		while (rewInterval > 0) {
			videoDisp.dispatchEvent( new Event(type) );
		}
 */
		return;

	}

	private function rewindForward():void
	{
		//	TODO:
		//	Forward rewind doesn't work for .mp4 files..?  Needs to be redone..
		
		trace("--------------------------------------------------------------------");
		trace("rewind  >>");
		trace("--------------------------------------------------------------------");
		//stateLogger();

		if (state == VideoEvent.DISCONNECTED)
		{
			return;
		}
		
		var newPoint:Number;
		
		//	for H264 format
		if (seekpoints)
		{
			//	determine nearest seekpoint
			//	TODO: make it more intelligent, starting not just from the beginning but from more close index..
			for (var i:int = 0; i < seekpoints.length; i++) {
				if (seekpoints[i].time > playheadTime) {
					targetSeekpoint = seekpoints[i].time;
					break;
				}
			}
			
			newPoint = targetSeekpoint + REWIND_FORWARD_INTERVAL;
			trace("forward FROM: "+playheadTime+"  TO: "+newPoint);
			playheadTime = newPoint;
			return;
		}
		
		//	for FLV format
		newPoint = playheadTime + REWIND_FORWARD_INTERVAL;
		trace("forward FROM: "+playheadTime+"  TO: "+newPoint);
		playheadTime = newPoint;
	}

	public override function stop() : void
	{
        //trace( "STOPPED" );
        stateLogger();
        super.stop();
        togglePlayPause( VideoEvent.PAUSED, true );// just to update UI
	}

	private function togglePlayerStates() : void
	{
		switch (state)
		{
		    case VideoEvent.PLAYING:
		        trace("toggling: from '"+VideoEvent.PLAYING+"' to '"+VideoEvent.PAUSED+"'");
		        togglePlayPause( VideoEvent.PAUSED );
		        break;
		
		    case VideoEvent.PAUSED:
		        trace("toggling: from '"+VideoEvent.PAUSED+"' to '"+VideoEvent.PLAYING+"'");
		        togglePlayPause( VideoEvent.PLAYING );
		        break;
		
		    default:
		        trace("toggling: state isn't on list - just play()...");
		        //	actions for rest of the states
/*
		        videoDisp.play();
		        btnPlayPause.label = LABEL_PAUSE;
		        btnPlayPause.toolTip = TOOLTIP_PAUSE;
 */
		        togglePlayPause( VideoEvent.PLAYING );
		}
	}

	/**
	 * 	Method for toggling Play/Pause, for both the video and controls (e.g. buttons).
	 *
	 * 	@param toState	toggle to which state: either VideoEvent.PLAYING or VideoEvent.PAUSE
	 * 	@param uiOnly	if only UI controls should be updated, with no actions on video taken
	 */
	private function togglePlayPause(toState:String, uiOnly:Boolean = false):void
	{
		if (toState == VideoEvent.PLAYING) {
			if (!source) {
				Alert.show("Please specify source URL of media to play.", "Warning");
				return;
			}
			if (!uiOnly) {
	        	play();
			}
	        //btnPlayPause.label = LABEL_PAUSE;
	        //btnPlayPause.toolTip = TOOLTIP_PAUSE;
		}
		else if (toState == VideoEvent.PAUSED) {
			if (!uiOnly) {
		        pause();
			}
	        //btnPlayPause.label = LABEL_PLAY;
	        //btnPlayPause.toolTip = TOOLTIP_PLAY;
		}
		else {
	        trace("togglePlayPause(): Wrong parameter toState=" + toState);
		}
	}


	private function playVideo():void
	{
	}


    }
}