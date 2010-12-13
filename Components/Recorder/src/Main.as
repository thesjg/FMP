import com.flixn.media.Recorder;
import com.flixn.services.RecorderServices;
import com.flixn.events.ServicesEvent;
import com.flixn.statistics.RecorderStatistics;

import com.flixn.recorder.controller.Playback;
import com.flixn.recorder.model.Settings;

import mx.rpc.events.*;

[Bindable]
public var settings:Settings;

[Bindable]
public var playback:Playback;

private var sLoaded:Boolean = false;

private function init():void
{
    trace('Initializing component');

    settings = new Settings(this);
    settings.addEventListener(ResultEvent.RESULT, settingsLoaded);
	
	/* We will be loaded soon enough, push a load event to statistics */
	RecorderStatistics.addLoad();
}

private function settingsLoaded(event:ResultEvent):void
{
    trace('Settings were loaded - mode: ' + settings.componentStyle);

    sLoaded = true;
    setupDisplay();
}

private function setupDisplay():void
{
    if (sLoaded == true) {
        this.currentState = (settings.componentStyle == 'video') ? 'videoRecorder'
                                                                 : 'audioRecorder';

        if (settings.videoEnable == true) {
            playback = new Playback(this.VideoView.display, this.VideoView.controls);
        } else {
            playback = new Playback(null);
        }
    }
}