package com.flixn.player.controller
{
    import com.flixn.component.browserapi.BrowserAPI;
    import com.flixn.player.model.Settings;
    import com.flixn.player.view.Menu;

    import flash.display.Stage;
    import flash.display.StageDisplayState;

    import mx.managers.SystemManager;

    public class Menu
    {
        public var onMenuClose:Function;

        private var _mView:com.flixn.player.view.Menu;
        private var _browserAPI:BrowserAPI;
        private var _settings:Settings;

        public function Menu(menu:com.flixn.player.view.Menu):void
        {
            _mView = menu;
            _browserAPI = new BrowserAPI();
            _settings = new Settings();
        }

        public function emailButtonClick():void
        {
            _showEmail();
        }

        public function embedButtonClick():void
        {
            _showEmbed();
        }

        public function shareButtonClick():void
        {
            _showShare();
        }

        public function infoButtonClick():void
        {
            _showInfo();
        }

        public function smsButtonClick():void
        {
            _showSMS();
        }

        public function fullscreenButtonClick():void
        {
            try {
                if (_mView.stage.displayState == StageDisplayState.NORMAL)
                    _mView.stage.displayState = StageDisplayState.FULL_SCREEN;
                else
                    _mView.stage.displayState = StageDisplayState.NORMAL;

                closeButtonClick();
            } catch (err:SecurityError) {
                // ignore
            }
        }

        public function popoutButtonClick():void
        {
            var url:String = 'http://components.flixn.evilprojects.net/support/player/popout/${componentId}/${mediaId}';

            url = url.replace('${componentId}', _settings.componentId);
            url = url.replace('${mediaId}', _settings.mediaId);

            _browserAPI.createPopup(url, 768, 495);

            _mView.cpView.panel.pauseButtonClick();
        }

        public function lowerlightsButtonClick():void
        {
            if (_browserAPI.lightsLowered == false) {
                _browserAPI.lowerLights();
                _mView.lowerlights_button.toolTip = 'Raise Lights';
            } else {
                _browserAPI.raiseLights();
                _mView.lowerlights_button.toolTip = 'Lower Lights';
            }
        }

        public function closeButtonClick():void
        {
            _mView.visible = false;
            hidePanels();
            _mView.cpView.panel.freeOpen();
        }

        public function hidePanels():void
        {
            _mView.email_panel.visible = false;
            _mView.embed_panel.visible = false;
            _mView.info_panel.visible = false;
            _mView.share_panel.visible = false;
            _mView.sms_panel.visible = false;
        }

        private function _showEmail():void
        {
            if (_mView.email_panel.visible == true) {
                _mView.email_panel.visible = false;
            } else {
                hidePanels();
                _mView.email_panel.visible = true;
            }
        }

        private function _showEmbed():void
        {
            if (_mView.embed_panel.visible == true) {
                _mView.embed_panel.visible = false;
            } else {
                hidePanels();
                _mView.embed_panel.visible = true;
            }
        }

        private function _showInfo():void
        {
            if (_mView.info_panel.visible == true) {
                _mView.info_panel.visible = false;
            } else {
                hidePanels();
                _mView.info_panel.visible = true;
            }
        }

        private function _showShare():void
        {
            if (_mView.share_panel.visible == true) {
                _mView.share_panel.visible = false;
            } else {
                hidePanels();
                _mView.share_panel.visible = true;
            }
        }

        private function _showSMS():void
        {
            if (_mView.sms_panel.visible == true) {
                _mView.sms_panel.visible = false;
            } else {
                hidePanels();
                _mView.sms_panel.visible = true;
            }
        }
    }
}