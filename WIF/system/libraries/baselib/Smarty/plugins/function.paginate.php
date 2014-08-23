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
 * Name:     paginate<br>
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

function smarty_function_paginate($aParam, &$smarty)
{
    // {paginate count=30 curr=$currentPage max=5 url="test.php?page=::PAGE::" start_text='首页' prev_text="&#171; 上一页" next_text="下一页 &#187;" end_text='末页' goto_text="前往页面：" etc_text="..."}

    $nItemCnt = $aParam['count'];
    $nStart = $aParam['start'];
    $nMaxPage = $aParam['max'];
    $sUrl = $aParam['url'];
	$nPageSize = empty($aParam['page_size']) ? 25 : $aParam['page_size'];

	$sStartText = empty($aParam['start_text']) ? 'First' : $aParam['start_text'];
    $sEndText = empty($aParam['end_text']) ? 'Last' : $aParam['end_text'];
	$sPrevText = empty($aParam['prev_text']) ? '&#171; Next' : $aParam['prev_text'];
    $sNextText = empty($aParam['next_text']) ? 'Prev &#187;' : $aParam['next_text'];
    $sGotoText = empty($aParam['goto_text']) ? 'Goto ：' : $aParam['goto_text'];
    $sEtcText = !isset($aParam['etc_text']) ? '&#8230;' : $aParam['etc_text'];

    
    $bDrewDots = true;
	//var_dump($nStart,$nPageSize);
	$nCurrPage = intval(floor($nStart/$nPageSize))+1;
	
	$nPageCnt = intval(ceil($nItemCnt/$nPageSize));

	
	/*if($nCurrPage<1){
		$nCurrPage = 1;
	}
	if($nCurrPage>$nPageCnt){
		$nCurrPage = $nPageCnt;
	}*/
	if($nPageCnt <= 1){
		return '';
	}

    if ($nPageCnt > $nMaxPage)
    {
        if (1 > ($nCurrPage - ($nMaxPage / 2)))
        {
            $nPageStart = 1;
            $nPageEnd = $nMaxPage;
        }
		elseif ($nPageCnt < ($nCurrPage + ($nMaxPage / 2)))
        {
            $nPageStart = $nPageCnt - $nMaxPage+1;
            $nPageEnd = $nPageCnt;
        }
        else
        {
            $nPageStart = $nCurrPage - ceil($nMaxPage / 2)+1;
            $nPageEnd = $nCurrPage + ceil($nMaxPage / 2);
        }
    }
    else
    {
        $nPageStart = 1;
        $nPageEnd = $nPageCnt;
    }

    $sOut = '';
    //首页
    $sOut .= sfp_link($sStartText,1,$sStartText,$sUrl,'previous',$nPageSize);

	//上一页
    if ($nCurrPage == 1)
    {
		$sOut .= sfp_link($sGotoText,1,$sPrevText,'','previous-off',$nPageSize);
    }
    else
    {
		//$sOut .= sfp_link($sGotoText,1,$sStartText,$sUrl,'');
		$sOut .= sfp_link($sGotoText,$nCurrPage - 1,$sPrevText,$sUrl,'previous',$nPageSize);
    }
    if ($nPageStart > 1)
    {
        $sOut .= "<li>$sEtcText</li>\n";
    }
	//页数
    for ($i = $nPageStart; $i <= $nPageEnd; $i++)
    {
        if ($i == $nCurrPage)
        {
            $sOut .= sfp_link($sGotoText,$i,$i,$sUrl,'active',$nPageSize);//"<a href='$sUrl1' title='$sGotoText $i'><span class='current'>$i</span></a>";
        }
        else
        {
            $sOut .= sfp_link($sGotoText,$i,$i,$sUrl,'',$nPageSize);//"<a href='$sUrl1' title='$sGotoText $i'>$i</a>";
        }
    }

	//下一页
	if ($nPageEnd < $nPageCnt)
    {
        $sOut .= "<li>$sEtcText</li>\n"; 
    }

	if ($nCurrPage >= $nPageCnt)
    {
		$sOut .= sfp_link($sGotoText,$nCurrPage,$sNextText,$sUrl,'next-off',$nPageSize);
    }
    else
    {

		$sOut .= sfp_link($sGotoText,$nCurrPage+1,$sNextText,$sUrl,'next',$nPageSize);
		//$sOut .= sfp_link($sGotoText,$nPageCnt,$sEndText,$sUrl,'');
    }

    //末页
    $sOut .= sfp_link($sEndText,$nPageCnt,$sEndText,$sUrl,'next',$nPageSize);


	$sOut = "<ul id='pagination-clean'>\n".$sOut . "</ul>";
    return $sOut;
} //smarty_paginate 

function sfp_link($goto_text,$tn,$i,$url,$class='',$nPageSize)
{
	if($class ==''||$class =='next'||$class =='previous')
	{
		$url_temp = str_replace('::PAGE::', ($tn), $url);
		return "<li><a href='$url_temp' title='$goto_text $tn'>$i</a></li>\n";
	}
	else
	{
		return "<li class='".$class."'>$i</li>\n";
	}
}
/* vim: set expandtab: */
