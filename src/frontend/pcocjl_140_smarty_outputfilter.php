<?php
/**
 * Manipulate DOM for LoadCSS and CriticalCSS and
 * load JS at the end of HTML BODY tag.
 *
 * @author PixelCrab <cs@pixelcrab.at>
 * @copyright 2018 PixelCrab
 *
 * @global JTLSmarty $smarty
 * @global Plugin $oPlugin
 */

if (class_exists('Shop')) {
    require_once $oPlugin->cFrontendPfad . '../include/class.pcocjl.helper.php';
    $pcocjlHelper = pcocjlHelper::getInstance($oPlugin, $smarty);

    // Prepend default critical css from evo template without colors. Our CSS file will overwrite the stuff when it's loaded
    if ($pcocjlHelper->getConfig('use_criticalcss')) {
        $pcocjlHelper->insertCriticalCss();
    }

    // Append loadCss JavaScript into head
    if ($pcocjlHelper->getConfig('use_loadcss')) {
        // @todo Maybe we should make this step after moving JS
        $pcocjlHelper->insertLoadCss();
    }
    
    // Move JS stuff into body
    if ($pcocjlHelper->getConfig('move_js_to_body')) {
        $pcocjlHelper->moveJsToBodyEnd();
    }
}
