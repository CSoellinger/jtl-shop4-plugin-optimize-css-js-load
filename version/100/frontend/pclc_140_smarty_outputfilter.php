<?php
/**
 * Manipulate DOM for LoadCSS and CriticalCSS
 *
 * @author PixelCrab <cs@pixelcrab.at>
 * @copyright 2018 PixelCrab
 * 
 * @global JTLSmarty $smarty
 * @global Plugin $oPlugin
 */

if (class_exists('Shop')) {
    require_once $oPlugin->cFrontendPfad . '../include/class.pclc.helper.php';
    $pclcHelper = pclcHelper::getInstance($oPlugin, $smarty);

    if ($pclcHelper->getConfig('use_criticalcss')) {
        // Prepend default critical css from evo template without colors. Our CSS file will overwrite the stuff when it's loaded
        $pclcHelper->insertCriticalCss();
    }
    
    if ($pclcHelper->getConfig('use_loadcss')) {
        // Append loadCss JavaScript into head
        $pclcHelper->insertLoadCss();
    }
}
