<?php
/**
 * Smarty plugin
 *传入参数note_id,note_title
 *返回形如/note/note_name_n100.html
 */

function smarty_function_seourl($aParam, &$smarty)
{
    
    $note_id = $aParam['note_id'];
    $note_title = $aParam['note_title'];
	//干掉连续空格，用_
	$note_title = preg_replace('/\s(?=\s)/', '', $note_title); 		
	$note_title = preg_replace('/[\n\r\t]/', ' ', $note_title); 
	$note_title = str_replace(' ','_',$note_title);
		
				
	$note_title = urlencode($note_title);
	$note_title = str_replace('%','_',$note_title);
	
	return '/note/'.$note_title.'_n'.$note_id.'.html';
} //smarty_seourl

