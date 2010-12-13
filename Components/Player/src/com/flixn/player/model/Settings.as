package com.flixn.player.model {

    import com.flixn.component.settings.SettingsCore;

    import mx.core.Application;
    import mx.rpc.events.ResultEvent;

    public class Settings extends SettingsCore
    {
        private static var _setupDefaults:Boolean = false;

        private static var _bufferTime:Number;
        private static var _panelTimeout:Number;

        private static var _autoPlay:Boolean;

        private static var _menuEnable:Boolean;
        private static var _tagEnable:Boolean;
        private static var _commentEnable:Boolean;

        private static var _embedEnable:Boolean;
        private static var _shareEnable:Boolean;
        private static var _emailEnable:Boolean;
        private static var _smsEnable:Boolean;
        private static var _infoEnable:Boolean;
        private static var _fullscreenEnable:Boolean;
        private static var _popoutEnable:Boolean;
        private static var _lightingEnable:Boolean;

        private static var _statisticsDisable:Boolean;

        /* XXX */
        private static var _mediaId:String;
        private static var _mediaTarget:String;

        /* XXX */
        private static var _moderationOverride:Boolean;

        public function Settings(root:Application = null)
        {
            if (!_setupDefaults) {
                addEventListener(ResultEvent.RESULT, settingsFileResult);
                super(root);

                _setupDefaults = true;

                _autoPlay = retrieveBooleanFlashVar('autoPlay', false);
                _bufferTime = retrieveNumericFlashVar('bufferTime', 5);
                _panelTimeout = retrieveNumericFlashVar('panelTimeout', 2000);

                _menuEnable = false;
                _tagEnable = false;
                _commentEnable = false

                _embedEnable = false;
                _shareEnable = false;
                _emailEnable = false;
                _smsEnable = false;
                _infoEnable = false;
                _fullscreenEnable = false;
                _popoutEnable = false;
                _lightingEnable = false;

                /* TODO: Implement me */
                _statisticsDisable = false;

                /* XXX */
                _mediaId = retrieveStringFlashVar('mediaId', '');
                //_mediaId = retrieveStringFlashVar('mediaId', 'qd07ty');
                //_mediaId = retrieveStringFlashVar('mediaId', '48afe5f5-05ec-0001-d0cc-aa06e903107a');
                //_mediaTarget = retrieveStringFlashVar('mediaTarget', 'HD High');

                _moderationOverride = retrieveBooleanFlashVar('moderationOverride', false);
            }
        }

        public function get autoPlay():Boolean
        {
            return _autoPlay;
        }

        public function get bufferTime():Number
        {
            return _bufferTime;
        }

        public function get panelTimeout():Number
        {
            return _panelTimeout;
        }

        public function get menuEnable():Boolean
        {
            return _menuEnable;
        }

        public function get tagEnable():Boolean
        {
            return _tagEnable;
        }

        public function get commentEnable():Boolean
        {
            return _commentEnable;
        }

        public function get embedEnable():Boolean
        {
            return _embedEnable;
        }

        public function get shareEnable():Boolean
        {
            return _shareEnable;
        }

        public function get emailEnable():Boolean
        {
            return _emailEnable;
        }

        public function get smsEnable():Boolean
        {
            return _smsEnable;
        }

        public function get infoEnable():Boolean
        {
            return _infoEnable;
        }

        public function get fullscreenEnable():Boolean
        {
            return _fullscreenEnable;
        }

        public function get popoutEnable():Boolean
        {
            return _popoutEnable;
        }

        public function get lightingEnable():Boolean
        {
            return _lightingEnable;
        }


        /* XXX */
        public function get mediaId():String
        {
            return _mediaId;
        }

        public function get mediaTarget():String
        {
            return _mediaTarget;
        }

        public function get moderationOverride():Boolean
        {
            return _moderationOverride;
        }

        private function settingsFileResult(event:ResultEvent):void
        {
            var result:XML = new XML(event.message.body);

            for each (var feature:XML in result.features.*) {
                switch (feature.toString()) {
                    case 'embed':
                        _embedEnable = retrieveBooleanFlashVar('embedEnable', true);
                        _menuEnable = true;
                        break;
                    case 'share':
                        _shareEnable = retrieveBooleanFlashVar('shareEnable', true);
                        _menuEnable = true;
                        break;
                    case 'email':
                        _emailEnable = retrieveBooleanFlashVar('emailEnable', true);
                        _menuEnable = true;
                        break;
                    case 'sms':
                        _smsEnable = retrieveBooleanFlashVar('smsEnable', true);
                        _menuEnable = true;
                        break;
                    case 'info':
                        _infoEnable = retrieveBooleanFlashVar('infoEnable', true);
                        _menuEnable = true;
                        break;
                    case 'fullscreen':
                        _fullscreenEnable = retrieveBooleanFlashVar('fullscreenEnable', true);
                        _menuEnable = true;
                        break;
                    case 'lighting':
                        _lightingEnable = retrieveBooleanFlashVar('lightingEnable', true);
                        _menuEnable = true;
                        break;
                    case 'popout':
                        _popoutEnable = retrieveBooleanFlashVar('popoutEnable', true);
                        _menuEnable = true;
                        break;
                    default:
                        break;
                }
            }
        }
    }
}
