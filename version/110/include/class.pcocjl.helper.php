<?php
/**
 * Plugin helper with all the magic.
 *
 * @author PixelCrab <cs@pixelcrab.at>
 * @copyright 2018 PixelCrab
 */

class pcocjlHelper
{
    
    /**
     * @var null|pcocjlHelper Self instance
     */
    private static $_instance = null;
    
    /**
     * @var null|Plugin Plugin instance
     */
    private $plugin = null;

    /**
     * @var null|JTLSmarty Smarty instance
     */
    private $smarty = null;
    
    /**
     * constructor
     *
     * @param Plugin $oPlugin
     */
    public function __construct(Plugin $oPlugin, JTLSmarty $smarty)
    {
        $this->plugin = $oPlugin;
        $this->smarty = $smarty;
    }
    
    /**
     * singleton getter
     *
     * @param Plugin $oPlugin
     * @return pcocjlHelper
     */
    public static function getInstance(Plugin $oPlugin, JTLSmarty $smarty)
    {
        return (self::$_instance === null) ? new self($oPlugin, $smarty) : self::$_instance;
    }
    
    /**
     * Replace all CSS sheet references with rel="preload" and add LoadCSS as polyfill fallback.
     *
     * @return pcocjlHelper $this
     */
    public function insertLoadCss()
    {
        include_once 'pcocjl_load-css-polyfill.php';
        
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
     * @return pcocjlHelper $this
     */
    public function insertCriticalCss()
    {
        include_once 'pcocjl_critical-css.php';

        pq('head')->prepend('<style>' . $criticalCss . '</style>');

        return $this;
    }

    /**
     * Fetch and move JS stuff to the end from BODY HTML tag.
     *
     * @return pcocjlHelper $this
     */
    public function moveJsToBodyEnd()
    {
        // First fetch all scripts from HEAD
        if (pq('head script')->count() > 0) {
            $scriptsHead = pq('head script');
            // and remove them
            pq('head script')->remove();
        }
        
        // Second fetch all scripts from BODY
        if (pq('body script')->count() > 0) {
            $scriptsBody = pq('body script');
            // and remove them
            pq('body script')->remove();
        }

        // At least append them to body so that they are here again
        pq('body')
            ->append($scriptsHead)
            ->append($scriptsBody);

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
        return $this->plugin->oPluginEinstellungAssoc_arr['pcocjl_' . $cfg];
    }

}
