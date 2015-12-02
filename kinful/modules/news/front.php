<?php
/********************************************************/
/*****                 @!!@                          ****/
/********************************************************/
/**
 *@FileName : module/news/front.php
 *@Author	: WangKilin
 *@Email	: wangkilin@126.com
 *@Date		: 2010-7-23
 *@Homepage	: http://www.kinful.com
 *@Version	: 0.1
 */
defined('Kinful') or die("forbidden");

require_once(dirname(__FILE__) . '/class/module_news_front.class.php');

function news_output ($moduleInfo, $parameterExpressionString = null)
{
    global $my_client_type;

    $MyNewsModule = new My_News_Module ($moduleInfo);
    if ($parameterExpressionString) {
        $_set = MY_LOAD_MAIN_BLOCK_CONTENT === $parameterExpressionString ? 'setFinalPage' : 'setParams';

        $MyNewsModule->$_set($parameterExpressionString);
    }

    switch ($my_client_type) {
        case MY_CLIENT_IS_WAP:

            return $MyNewsModule->returnWap ();

            break;

        case MY_CLIENT_IS_RSS:

            return $MyNewsModule->returnRss ();

            break;

        default:

            return $MyNewsModule->returnWeb ();

            break;
    }
}

/* EOF */