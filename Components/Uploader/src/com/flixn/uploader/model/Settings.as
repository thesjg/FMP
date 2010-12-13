package com.flixn.uploader.model {

    import com.flixn.component.settings.SettingsCore;

    import mx.core.Application;

    public class Settings extends SettingsCore {

        public function Settings(root:Application = null)
        {
            super(root);

            //_videoEnable = retrieveBooleanFlashVar('videoEnable', true);
        }
    }
}