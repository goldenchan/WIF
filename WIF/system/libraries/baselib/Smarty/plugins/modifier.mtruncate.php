<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty mtruncate modifier plugin
 *
 * Type:     modifier<br>
 * Name:     mtruncate<br>
 * Purpose:  mTruncate a string to a certain length if necessary,
 *           optionally splitting in the middle of a word, and
 *           appending the $etc string or inserting $etc into the middle.
 * @link http://smarty.php.net/manual/en/language.modifier.truncate.php
 *          truncate (Smarty online manual)
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @param string
 * @param integer
 * @param string
 * @param string
 * @param boolean
 * @param boolean
 * @return string
 */
function smarty_modifier_mtruncate($string, $length = 80, $encoded = "utf-8", $etc = '...',
                                  $break_words = false, $middle = false)
{
    if ($length == 0)
        return '';

    if (mb_strlen($string, $encoded) > $length) {
        $length -= min($length, mb_strlen($etc, $encoded));
        if (!$break_words && !$middle) {
            $string = mb_substr($string, 0, $length+1, $encoded);//preg_replace('/\s+?(\S+)?$/', '', mb_substr($string, 0, $length+1, $encoded));
        }
        if(!$middle) {
            return mb_substr($string, 0, $length, $encoded).$etc;
        } else {
            return mb_substr($string, 0, $length/2, $encoded) . $etc . mb_substr($string, -$length/2, $encoded);
        }
    } else {
        return $string;
    }
}

/* vim: set expandtab: */

?>
