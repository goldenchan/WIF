<?php
/**
 * Smarty plugin
 * 
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty {paginate} function plugin
 * 
 * Type:     function<br>
 * Name:     escapetime<br>
 * Date:     2008-8-26<br>
 * Purpose:  create html for paginate
 * 
 * @link {paginate}
 * @author HonestQiao 
 * @version 1.0
 * @param array $ 
 * @param Smarty $ 
 * @return string output from {paginate}
 */

function smarty_function_escapetime($aParam, &$smarty)
{
    // {escapetime time=12552845  just="just"  minute="minute" hour="hour" day='day' month="month" }
	
	$result=false;
		 if(isset($aParam['time']))
		 {
		     $diff_time=(time()-$aParam['time']);
		   
		     if($diff_time<=60)
		     {
		    
		         $result=$aParam['just'];
				 
		     }elseif($diff_time>60 && $diff_time<=3600)
		     {
		        $minute=ceil($diff_time/60);
			    $result=array('count'=>$minute,'unit'=>$aParam['minute']);
				
		     }elseif($diff_time>3600 && $diff_time<=86400)
		     {
		        $minute=floor($diff_time/3600);
			    $result=array('count'=>$minute,'unit'=>$aParam['hour']);
		     }elseif($diff_time>86400 && $diff_time<=2592000)
			 {
			     $minute=floor($diff_time/86400);
				 $result=array('count'=>$minute,'unit'=>$aParam['day']);
			 
			 }elseif($diff_time>2592000)
			 {
			     $minute=floor($diff_time/2592000);
				 $result=array('count'=>$minute,'unit'=>$aParam['month']); 
			 }
		 }
		 if($diff_time>60)
		 {
		     $result=$result['count'].$result['unit'].'前';
		 }
		 return $result;

		 
		 
    
}
