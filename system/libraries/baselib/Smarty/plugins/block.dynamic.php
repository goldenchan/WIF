<?php

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty {dynamic}{/dynamic} block plugin
 *
 * Type:     block function<br>
 * Name:     dynamic<br>
 * Purpose:  show dynamic content<br>
 * @param array
 * @param string contents of the block
 * @param Smarty clever simulation of a method
 * @return string string $content 
 */
	function smarty_block_dynamic($param, $content, &$smarty) {
    	return $content;
	}	
?>