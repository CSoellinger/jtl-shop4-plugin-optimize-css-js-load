<?php
/**
 * Plugin helper with all the magic.
 *
 * @author PixelCrab <cs@pixelcrab.at>
 * @copyright 2018 PixelCrab
 */


class pclcHelper
{
    
    /**
     * @var null|pclcHelper Self instance
     */
    private static $_instance = null;
    
    /**
     * @var null|Plugin Plugin instance
     */
    private $plugin = null;
    
    /**
     * @var null|Smarty Smarty instance
     */
    private $smarty = null;
    
    /**
     * constructor
     *
     * @param Plugin $oPlugin
     */
    public function __construct(Plugin $oPlugin, $smarty)
    {
        $this->plugin = $oPlugin;
        $this->smarty = $smarty;
    }
    
    /**
     * singleton getter
     *
     * @param Plugin $oPlugin
     * @return pclcHelper
     */
    public static function getInstance(Plugin $oPlugin, $smarty)
    {
        return (self::$_instance === null) ? new self($oPlugin, $smarty) : self::$_instance;
    }
    
    /**
     * Replace all CSS sheet references with rel="preload" and add LoadCSS as polyfill fallback.
     *
     * @return pclcHelper
     */
    public function insertLoadCss()
    {
        include_once 'pclc_load-css-polyfill.php';
        
        // First append LoadCSS polyfill
        pq('head')->append('<script>' . $loadCssPolyfillJs . '</script>');
        
        // Callback for all CSS link items
        $cb = function ($key, $item) {
            $el = pq($item);
            $href = $el->attr('href');
            
            $newEl = '<link rel="preload" href="' . $href . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">';
            $newEl .= '<noscript><link rel="stylesheet" href="' . $href . '"></noscript>';
            
            $el->replaceWith($newEl);
        };
        
        // At least we replace all default css links in the head with rel"preload" and fallback.
        phpQuery::each(pq('head link[rel=stylesheet]'), $cb);
        
        return $this;
    }
    
    /**
     * Insert critical css, extracted from default evo template, into head.
     *
     * @return pclcHelper
     */
    public function insertCriticalCss()
    {
        include_once 'pclc_critical-css.php';

        pq('head')->prepend('<style>' . $criticalCss . '</style>');

        return $this;
    }
    
    /**
     * Get a config variable
     *
     * @param String $cfg
     * @return String
     */
    public function getConfig($cfg)
    {
        return $this->plugin->oPluginEinstellungAssoc_arr['pclc_' . $cfg];
    }

}
