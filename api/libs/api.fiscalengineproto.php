<?php


abstract class FiscalEngineProto {
    const URL_RORODOG           = '?module=rorodog';
    const RT_RENDER_ENGINE_UI   = 'renderengineui';
    const MISC_ENGINE_NAME      = 'enginename';

    public static function getUILink() {
        return (wf_Link(static::URL_RORODOG . '&' . static::RT_RENDER_ENGINE_UI . '=true&' . static::MISC_ENGINE_NAME . '=' . get_called_class(),
                        wf_img_sized('/skins/menuicons/rorodog.png', '', '16', '16'))
               );
    }

    public abstract function renderUI();
    public abstract function printReceipt();
}