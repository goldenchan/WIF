<?php
/**
 * http 缓存类
 * @file http_cache.php
 * @package tempalte
 * @author 陈金(wind.golden@gmail.com)
 */
//In RSS/ATOM feedMode, contains the date of the clients last update.
$clientCacheDate = 0; //Global public variable because PHP4 does not allow conditional arguments by reference
$_sessionMode = false; //Global private variable

/**
 * http cache 处理类
 * Enable support for HTTP/1.x conditional requests in PHP.
 * Goal: Optimisation
 * - If the client sends a HEAD request, avoid transferring data and return the correct headers.
 * - If the client already has the same version in its cache, avoid transferring data again (304 Not Modified).
 * - Possibility to control cache for client and proxies (public or private policy, life time).
 * - When $feedMode is set to true, in the case of a RSS/ATOM feed,
 *   it puts a timestamp in the global variable $clientCacheDate to allow the sending of only the articles newer than the client's cache.
 * - When $compression is set to true, compress the data before sending it to the client and persitent connections are allowed.
 * - When $session is set to true, automatically checks if $_SESSION has been modified during the last generation the document.
 *
 * Interface:
 * - function httpConditional($UnixTimeStamp,$cacheSeconds=0,$cachePrivacy=0,$feedMode=false,$compression=false)
 *  [Required] $UnixTimeStamp: Date of the last modification of the data to send to the client (Unix Timestamp format).
 *  [Implied] $cacheSeconds=0: Lifetime in seconds of the document. If $cacheSeconds>0, the document will be cashed and not revalidated against the server for this delay.
 *  [Implied] $cachePrivacy=0: 0=private, 1=normal (public), 2=forced public. When public, it allows a cashed document ($cacheSeconds>0) to be shared by several users.
 *  [Implied] $feedMode=false: Special RSS/ATOM feeds. When true, it sets $cachePrivacy to 0 (private), does not use the modification time of the script itself, and puts the date of the client's cache (or a old date from 1980) in the global variable $clientCacheDate.
 *  [implied] $compression=false: Enable the compression and allows persistant connections (automatic detection of the capacities of the client).
 *  [implied] $session=false: To be turned on when sessions are used. Checks if the data contained in $_SESSION has been modified during the last generation the document.
 *  Returns: True if the connection can be closed (e.g.: the client has already the lastest version), false if the new content has to be send to the client.
 *
 * Typical use:
 *
 *  require('http-conditional.php');
 *  //Date of the last modification of the content (Unix Timestamp format).
 *  //Examples: query the database, or last modification of a static file.
 *  $dateLastModification=...;
 *  if (httpConditional($dateLastModification))
 *  {
 *   ... //Close database connections, and other cleaning.
 *   exit(); //No need to send anything
 *  }
 *  //Do not send any text to the client before this line.
 *  ... //Rest of the script, just as you would do normally.
 *
 *
 * Version 1.6.1, 2005-04-03, http://alexandre.alapetite.net/doc-alex/php-http-304/
 *
 * ------------------------------------------------------------------
 * Written by Alexandre Alapetite, http://alexandre.alapetite.net/cv/
 *
 * Copyright 2004-2005, Licence: Creative Commons "Attribution-ShareAlike 2.0 France" BY-SA (FR),
 * http://creativecommons.org/licenses/by-sa/2.0/fr/
 * http://alexandre.alapetite.net/divers/apropos/#by-sa
 * - Attribution. You must give the original author credit
 * - Share Alike. If you alter, transform, or build upon this work,
 *   you may distribute the resulting work only under a license identical to this one
 * - The French law is authoritative
 * - Any of these conditions can be waived if you get permission from Alexandre Alapetite
 * - Please send to Alexandre Alapetite the modifications you make,
 *   in order to improve this file for the benefit of everybody
 *
 * If you want to distribute this code, please do it as a link to:
 * http://alexandre.alapetite.net/doc-alex/php-http-304/
 */
class Http_Cache {
    /**
     * 设置httpConditional
     * @param integer $UnixTimeStamp last modified unix 时间戳
     * @param integer $cacheSeconds 缓存秒数
     * @param integer $cachePrivacy privacy
     * @param boolean $feedMode
     * @param boolean $compression 是否压缩
     * @param bollean $session etag 是否加上session信息
     * @return boolean true or false
     */
    static function httpConditional($UnixTimeStamp, $cacheSeconds = 0, $cachePrivacy = 0, $feedMode = false, $compression = false, $session = false) {
        //Credits: http://alexandre.alapetite.net/doc-alex/php-http-304/
        //RFC2616 HTTP/1.1: http://www.w3.org/Protocols/rfc2616/rfc2616.html
        //RFC1945 HTTP/1.0: http://www.w3.org/Protocols/rfc1945/rfc1945.txt
        if (headers_sent()) return false;
        if (isset($_SERVER['SCRIPT_FILENAME'])) $scriptName = $_SERVER['SCRIPT_FILENAME'];
        elseif (isset($_SERVER['PATH_TRANSLATED'])) $scriptName = $_SERVER['PATH_TRANSLATED'];
        else return false;
        if ((!$feedMode) && (($modifScript = filemtime($scriptName)) > $UnixTimeStamp)) $UnixTimeStamp = $modifScript;
        $UnixTimeStamp = min($UnixTimeStamp, time());
        $is304 = true;
        $is412 = false;
        $nbCond = 0;
        //rfc2616-sec3.html#sec3.3.1
        $dateLastModif = gmdate('D, d M Y H:i:s \G\M\T', $UnixTimeStamp);
        $dateCacheClient = 'Thu, 10 Jan 1980 20:30:40 GMT';
        //rfc2616-sec14.html#sec14.19 //='"0123456789abcdef0123456789abcdef"'
        if (isset($_SERVER['QUERY_STRING'])) $myQuery = '?' . $_SERVER['QUERY_STRING'];
        else $myQuery = '';
        if ($session && isset($_SESSION)) {
            global $_sessionMode;
            $_sessionMode = $session;
            $myQuery.= print_r($_SESSION, true) . session_name() . '=' . session_id();
        }
        $etagServer = '"' . md5($scriptName . $myQuery . '#' . $dateLastModif) . '"';
        if ((!$is412) && isset($_SERVER['HTTP_IF_MATCH'])) { //rfc2616-sec14.html#sec14.24
            $etagsClient = stripslashes($_SERVER['HTTP_IF_MATCH']);
            $is412 = (($etagClient != '*') && (strpos($etagsClient, $etagServer) === false));
        }
        if ($is304 && isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) { //rfc2616-sec14.html#sec14.25 //rfc1945.txt
            $nbCond++;
            $dateCacheClient = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
            $p = strpos($dateCacheClient, ';');
            if ($p !== false) $dateCacheClient = substr($dateCacheClient, 0, $p);
            $is304 = ($dateCacheClient == $dateLastModif);
        }
        if ($is304 && isset($_SERVER['HTTP_IF_NONE_MATCH'])) { //rfc2616-sec14.html#sec14.26
            $nbCond++;
            $etagClient = stripslashes($_SERVER['HTTP_IF_NONE_MATCH']);
            $is304 = (($etagClient == $etagServer) || ($etagClient == '*'));
        }
        if ((!$is412) && isset($_SERVER['HTTP_IF_UNMODIFIED_SINCE'])) { //rfc2616-sec14.html#sec14.28
            $dateCacheClient = $_SERVER['HTTP_IF_UNMODIFIED_SINCE'];
            $p = strpos($dateCacheClient, ';');
            if ($p !== false) $dateCacheClient = substr($dateCacheClient, 0, $p);
            $is412 = ($dateCacheClient != $dateLastModif);
        }
        if ($feedMode) { //Special RSS/ATOM
            global $clientCacheDate;
            $clientCacheDate = strtotime($dateCacheClient);
            $cachePrivacy = 0;
        }
        if ($is412) { //rfc2616-sec10.html#sec10.4.13
            header('HTTP/1.1 412 Precondition Failed');
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Content-Type: text/plain');
            echo "HTTP/1.1 Error 412 Precondition Failed: Precondition request failed positive evaluation\n";
            return true;
        }
        elseif ($is304 && ($nbCond > 0)) { //rfc2616-sec10.html#sec10.3.5
            header('HTTP/1.0 304 Not Modified');
            header('Etag: ' . $etagServer);
            if ($feedMode) header('Connection: close'); //Comment this line under IIS
            return true;
        }
        else { //rfc2616-sec10.html#sec10.2.1
            //rfc2616-sec14.html#sec14.3
            if ($compression) ob_start('_httpConditionalCallBack'); //Will check HTTP_ACCEPT_ENCODING
            //header('HTTP/1.0 200 OK');
            if ($cacheSeconds == 0) {
                $cache = 'private, must-revalidate, ';
                //$cacheSeconds=-1500000; //HTTP/1.0
                
            }
            elseif ($cachePrivacy == 0) $cache = 'private, ';
            elseif ($cachePrivacy == 2) $cache = 'public, ';
            else $cache = '';
            $cache.= 'max-age=' . floor($cacheSeconds);
            // header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T',time()+$cacheSeconds)); //HTTP/1.0 //rfc2616-sec14.html#sec14.21
            header('Cache-Control: ' . $cache); //rfc2616-sec14.html#sec14.9
            header('Last-Modified: ' . $dateLastModif);
            header('Etag: ' . $etagServer);
            if ($feedMode) header('Connection: close'); //rfc2616-sec14.html#sec14.10 //Comment this line under IIS
            return $_SERVER['REQUEST_METHOD'] == 'HEAD'; //rfc2616-sec9.html#sec9.4
            
        }
    }
    /**
     * ob start 回调函数
     * @param string $buffer  buffer
     * @param integer $mode gz压缩方式
     * @return $buffer
     *
     */
    function _httpConditionalCallBack($buffer, $mode = 5) {
        //Private function automatically called at the end of the script when compression is enabled
        //rfc2616-sec14.html#sec14.11
        //You can adjust the level of compression with zlib.output_compression_level in php.ini
        if (extension_loaded('zlib') && (!ini_get('zlib.output_compression'))) {
            $buffer2 = ob_gzhandler($buffer, $mode); //Will check HTTP_ACCEPT_ENCODING and put correct headers
            if (strlen($buffer2) > 1) //When ob_gzhandler succeeded
            $buffer = $buffer2;
        }
        header('Content-Length: ' . strlen($buffer)); //Allows persistant connections //rfc2616-sec14.html#sec14.13
        return $buffer;
    }
    /**
     * 刷新 httpConditional
     * @param integer $UnixTimeStamp  unix 时间戳
     */
    static function httpConditionalRefresh($UnixTimeStamp) {
        //Update HTTP headers if the content has just been modified by the client's request
        //See an example on http://alexandre.alapetite.net/doc-alex/compteur/
        if (headers_sent()) return false;
        if (isset($_SERVER['SCRIPT_FILENAME'])) $scriptName = $_SERVER['SCRIPT_FILENAME'];
        elseif (isset($_SERVER['PATH_TRANSLATED'])) $scriptName = $_SERVER['PATH_TRANSLATED'];
        else return false;
        $dateLastModif = gmdate('D, d M Y H:i:s \G\M\T', $UnixTimeStamp);
        if (isset($_SERVER['QUERY_STRING'])) $myQuery = '?' . $_SERVER['QUERY_STRING'];
        else $myQuery = '';
        global $_sessionMode;
        if ($_sessionMode && isset($_SESSION)) $myQuery.= print_r($_SESSION, true) . session_name() . '=' . session_id();
        $etagServer = '"' . md5($scriptName . $myQuery . '#' . $dateLastModif) . '"';
        header('Last-Modified: ' . $dateLastModif);
        header('Etag: ' . $etagServer);
    }
}
