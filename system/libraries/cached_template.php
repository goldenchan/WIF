<?php
/**
 *  缓存模版处理类文件
 *
 * @filename   : cached_template.php
 * @package template
 * @author: Chenjin (wind.golden@gmail.com)
 */
/** 
 * 模版缓存处理类
 * Extends the Template class to provide support for cached templated. This class adds
 * two additional methods that are not available in Template:
 *
 * - CachedTemplate::isCached() to know whether the current CachedTemplate is working on cached contents
 * - CachedTemplate::clearCache() to clear the current cached contents of this template
 * - CachedTemplate::getCreationTimestamp() to determine when the current cached version was created
 *
 * @see Template
 */
class Cached_Template extends Template {
    /**
     * Constructor.
     * @param string $templateFile 不带主题的模版文件路径
     * @param string $layout 模版主题
     * @param int $cacheLifetime How many seconds we would like to cache the template
     */
    function Cached_Template($templateFile, $layout = '', $cacheLifetime = 1800) {
        // create the Smarty object and set the security values
        parent::__construct($templateFile, $layout);
        $this->getSmarty()->caching = true;
        $this->getSmarty()->cache_lifetime = $cacheLifetime;
        $tmp = md5($templateFile);
        $this->getSmarty()->setCacheDir(TMP . WI_CONFIG::$smarty['cache_dir'] . DS . $tmp{0} . $tmp{1});
    }
    /**
     * Renders the template and returns the contents as an string
     * @param string $cacheId  缓存id
     * @return The result as an string
     */
    function fetch($cacheId = 0) {
        return $this->getSmarty()->fetch($this->getTemplateFile() , $cacheId);
    }
    /**
     * returns wether this template is cached or not
     *
     * @param string $cacheId The cache identifier
     * @return true if the template is cached or false otherwise
     */
    function isCached($cacheId = 0) {
        $isCached = $this->getSmarty()->isCached($this->getTemplateFile() , $cacheId);
        return $isCached;
    }
    /**
     * clears the cache whose id is $cacheId
     *
     * @param string $cacheId The id of the cache that we'd like to clear
     * @return nothing
     */
    function clearCache($cacheId = 0) {
        return $this->getSmarty()->clearCache($this->getTemplateFile() , $cacheId);
    }
    /**
     * Displays the result of rendering the template
     * @param string $cacheId The id of the cache that we'd like to clear
     * @return Always true
     */
    function display($cacheId = 0) {
        $this->getSmarty()->display($this->getTemplateFile() , $cacheId);
        return true;
    }
    /**
     * returns the date when this template was created
     *
     * @param the UNIX timestamp when this template was created
     */
    function getCreationTimestamp() {
        // if the page was just generated, smarty doesn't have this information
        if (isset($this->_cache_info['timestamp'])) $timestamp = $this->_cache_info['timestamp'];
        else $timestamp = time();
        return $timestamp;
    }
}
