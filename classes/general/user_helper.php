<?php


/**
 * Linemedia Autoportal
 * Main module
 * User helper class
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__);


/*
 * ¬спомогательный класс дл€ работы с пользовател€ми
 */
class LinemediaAutoUserHelper
{
    
    /**
     * Ќе сканирует ли авторизованный пользователь каталоги сайта?
     */
    public static function isRobot($event = 'LM_AUTO_HIT', $timeout = 60, $max_hits = 30, $module_id = 'linemedia.auto')
    {
        
        if (CUser::GetId() > 0) {
			$res = CEventLog::GetList(array(), array('USER_ID' => CUser::GetId(), 'AUDIT_TYPE_ID' => $event, 'TIMESTAMP_X_1' => date('d.m.Y H:i:s', time() - $timeout) ));
			$hits = $res->SelectedRowsCount();
			
			CEventLog::Add(array('USER_ID' => CUser::GetId(), 'AUDIT_TYPE_ID' => $event, 'MODULE_ID' => $module_id));
			
			if ($hits >= $max_hits) {
				return true;
			}
		}
		
		return false;
    }
    
    
    /**
     * ѕроверка поисковый ли робот выполн€ет запрос
     */
    public static function isSearchRobot($user_agent = false)
    {
	    if (CUser::GetId() > 0) {
	    	return false;
        } 
	    
	    /*
	     * —писок агентов
	     */
	    $agents = array(
	    	'Google',
			'Yandex',
			'Rambler',
			'Mail.ru',
			'Aport',
			'MSN',
			'Yahoo',
			'Altavista',
			'AOL',
			'NIGMA',
			'Zao-Crawler',
			'YottaShopping_Bot',
			'YM',
			'YandexBlog',
			'Yahoo-MMCrawler',
			'YahooFeedSeeker',
			'WinHttp-Autoproxy-Service',
			'Windows-RSS-Platform',
			'YaDirectBot',
			'Xenu Link Sleuth',
			'wish-la',
			'Wget',
			'WebZIP',
			'WebImages',
			'weblist',
			'webcrawl.net',
			'WebCopier',
			'webcollage',
			'Webbot.ru',
			'WebAlta Crawler',
			'Web Downloader',
			'voyager',
			'VisBot',
			'VadixBot',
			'updated',
			'Twiceler',
			'TurtleScanner',
			'TurnitinBot',
			'TulipChain',
			'TMCrawler',
			'TinEye',
			'SurveyBot',
			'Subscribe.Ru',
			'Speedy Spider',
			'sohu-search',
			'SoftSearch',
			'Snapbot',
			'snap.com beta crawler',
			'SMILESEOTools',
			'SMILE SEO Tools',
			'SiteScripts.com Link Checker',
			'ShopWiki',
			'Shim-Crawler',
			'sherlock',
			'shelob',
			'SeznamBot',
			'Sensis Web Crawler',
			'ScSpider',
			'Schmozilla',
			'SBIder',
			'RufusBot',
			'RSSreader.ru',
			'RSS Xpress',
			'RedTram.com',
			'RedBot',
			'Recentsoft.com PAD Spider',
			'QuepasaCreep ( crawler@quepasacorp.com )',
			'PTsecurity',
			'psbot',
			'ProjectWF-java-test-crawler',
			'Pompos',
			'POE-Component-Client-HTTP',
			'PlantyNet_WebRobot',
			'pipeLiner',
			'Pingdom GIGRIB',
			'PHP',
			'PHP version tracker',
			'Pete-Spider Light',
			'panscient.com',
			'PageBitesHyperBot',
			'PADLibrary Spider',
			'OrangeSpide',
	    );
	    
	    /*
	     *  акую строку-подпись агента надо проверить?
	     */
	    $user_agent = ($user_agent) ? ($user_agent) : ($_SERVER['HTTP_USER_AGENT']);
	    
	    foreach ($agents as $agent) {
		    if (strpos($user_agent, $agent) !== false) {
		    	return true;
            }
	    }
	    return false;
    }
    
    
}
