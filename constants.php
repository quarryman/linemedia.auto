<?php


/*
 * http://webservicepilot.tecdoc.net/pegasus-2-0/doc/InterfaceCatService.PDF
 * page 111
 * 11.1 (Get articles by brandno and generic articleid)
 * "numberType" description
 */
define('LM_AUTO_MAIN_ARTICLE_TYPE_ARTICLE',     0);
define('LM_AUTO_MAIN_ARTICLE_TYPE_OE',          1);
define('LM_AUTO_MAIN_ARTICLE_TYPE_TRADE',       2);
define('LM_AUTO_MAIN_ARTICLE_TYPE_COMPARABLE',  3);
define('LM_AUTO_MAIN_ARTICLE_TYPE_REPLACEMENT', 4);
define('LM_AUTO_MAIN_ARTICLE_TYPE_REPLACED',    5);
define('LM_AUTO_MAIN_ARTICLE_TYPE_EAN',         6);
define('LM_AUTO_MAIN_ARTICLE_TYPE_ANY',         10);


define('LM_AUTO_DEBUG_NOTICE', 1);
define('LM_AUTO_DEBUG_WARNING', 10);
define('LM_AUTO_DEBUG_USER_ERROR', 15);
define('LM_AUTO_DEBUG_ERROR', 20);
define('LM_AUTO_DEBUG_CRITICAL', 30);
