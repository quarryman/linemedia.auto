<?php
/**
 * Linemedia Autoportal
 * Main module
 * Debug all calculations
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
 
IncludeModuleLangFile(__FILE__); 

class LinemediaAutoDebug
{
	static $filename       = false;
	static $adm_messages   = array();
	
    
    /**
     * Основная функция дебага.
     */
    public static function add($message, $spoiler = false, $priority = LM_AUTO_DEBUG_NOTICE, $file = false, $line = false) 
    {
    	if (self::$filename) {
	    	return self::write2file($message, $spoiler, $priority);
    	}
    	
        /*
         * Время отладки.
         */
        $GLOBALS['last_debug_time'] = $GLOBALS['last_debug_time'] ? $GLOBALS['last_debug_time'] : microtime(true);
        
        $min_debug = ($_REQUEST['debug']) ? $_REQUEST['debug'] : LM_AUTO_DEBUG_WARNING;
        
        if ($priority < $min_debug) {
            return;
        }
        
        switch ($priority) {
            case LM_AUTO_DEBUG_WARNING:
            break;
               
            case LM_AUTO_DEBUG_USER_ERROR:
                $style = 'style="font-weight:bold;color:blue"';
                self::addInformer($message, $spoiler, $file, $line, $priority);
                break;
                
            case LM_AUTO_DEBUG_ERROR:
                $style = 'style="font-weight:bold"';
                
                if (isset($_GET['lm_debug'])) {
                	self::addInformer($message, $spoiler, $file, $line, $priority);
                }
                self::sendLMDebug($message, $spoiler, $file, $line);
                break;
                
            case LM_AUTO_DEBUG_CRITICAL:
                $style = 'style="font-weight:bold;color:red"';
                
                if (isset($_GET['lm_debug'])) {
                	self::addInformer($message, $spoiler, $file, $line, $priority);
                }
                self::sendLMDebug($message, $spoiler, $file, $line);
                break;
                
            default:
                $style = '';
                break;
        }
        

		/*
         * Прерываем работу отладки только если ошибка не критическая.
         * В прочих случаях она должна молча уйти на почту.
         */
        global $USER;
        
        if ($_GET['lm_auto_debug'] != 'Y' || !is_object($USER) || !$USER->IsAdmin()) {
            return;
        }
        
        
        /*
         * Если вместо текста передано событие.
         */
        if ($message instanceof Exception) {
	    	$exception = $message;
	    	$message = $exception->GetMessage();
    	}
    	
        
        /*
         * Прошло времени.
         */
        $now = microtime(true);
        $diff = ($now - $GLOBALS['last_debug_time']);
        $diff = sprintf('%.4f', $diff);
        $diff = ($diff > 0.1) ? "<b>$diff</b>" : $diff;
        
        $global_diff = $now - debug_start;
        $global_diff = sprintf('%.4f', $global_diff);
        
        
        $str  = '<div class="bx-component-debug" ' . $style . '>' . $message;
        $str .= "<br><nobr>$global_diff [last action took $diff s]</nobr>";
        
        if ($spoiler) {
            $id = 'lm_dbg_' . mt_rand(0, 99999999);
            $str .= " <a href='javascript:;' onclick=\"document.getElementById('$id').style.display = (document.getElementById('$id').style.display == 'none') ? 'block' : 'none'\">+</a><pre id='$id' style=\"display:none\">$spoiler</pre>";
        }
        $str .= "</div>";
        
        
        if (!defined('AJAX') AND !defined('LM_AUTO_DEBUG_SUPPRESS_OUTPUT')) {
            echo $str;
        }
        
        $GLOBALS['last_debug_time'] = $now;
    }
    
    
    /**
     * Запись в файл
     */
    public static function write2file($message, $spoiler = false, $priority = LM_AUTO_DEBUG_NOTICE)
    {
	    $line = date('d.m.Y H:i:s') . ' ' . strip_tags($message) . " " . strip_tags($spoiler) . "\n";//_d($line);
        try {
	        $h = fopen(self::$filename, 'a');
	        fwrite($h, $line);
	        fclose($h);
        } catch (Exception $e) {
	        return false;
        }
    }
    
    
    /**
     * Включен ли отладчик.
     */
    public static function enabled()
    {
    	if ($_GET['lm_auto_debug'] == 'Y') {
    		return true;
        }
        return false;
    }
    
    
    /**
     * Показывать ли отладчик.
     */
    public static function visible()
    {
        global $USER;
        
        if (!isset($USER)) {
            $USER = new CUser();
        }
        if (self::enabled() && $USER->IsAdmin()) {
            return true;
        }
        return false;
    }
    
    
    /**
     * Добавление видимого админу сообщения при серьёзных ощибках
     */
    public static function sendLMDebug($message, $spoiler, $file = false, $line = false)
    {
        @set_time_limit(0);
        
    	if ($message instanceof Exception) {
	    	$exception = $message;
	    	$message = $exception->GetMessage(); // тема письма
	    	
	    	$full_message = $exception->GetMessage() . "\n\nFile: " . $exception->getFile() . "\nLine: " . $exception->getLine() . "\n\nTrace:\n" . $exception->getTraceAsString();
    	} else {
	    	$full_message = $message . "\n\n\nFile: " . $file . " Line: " . $line;
    	}
    	
    	if ($spoiler) {
	    	$full_message .= "\n\nSpoiler: " . json_encode($spoiler);
        }
    	
    	if (CUser::GetID()) {
	    	$full_message .= "\n\nUser: " . CUser::GetLogin() . ' ['.CUser::GetID().'] ' . $_SERVER['SERVER_NAME'] . '/bitrix/admin/user_edit.php?lang=ru&ID=' . CUser::GetID();
    	} else {
    		$full_message .= "\n\nUser: unauthorized";
        }
    	
    	$full_message .= "\n\nServer: " . $_SERVER['SERVER_NAME'];
    	$full_message .= "\nUrl: " . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
    	$full_message .= "\nIP: " . $_SERVER['REMOTE_ADDR'];
    	$full_message .= "\nUser-Agent: " . $_SERVER['HTTP_USER_AGENT'];
    	$full_message .= "\nReferer: " . $_SERVER['HTTP_REFERER'];
    	
    	$full_message .= "\n\n\nMain module: " . LINEMEDIA_AUTO_MAIN_VERSION;
    	$full_message .= "\nRemote suppliers module: " . (defined('LINEMEDIA_AUTO_REMOTE_SUPPLIERS_VERSION') ? LINEMEDIA_AUTO_REMOTE_SUPPLIERS_VERSION : 'no');
    	
        
    	/*
    	 * Отслеживание коитических ошибок для скорейшего их исправления
    	 */
    	try {
    		$headers = 'From: '.$_SERVER['SERVER_NAME'].'@' . $_SERVER['SERVER_NAME'] . "\r\n" . 'X-Mailer: Linemedia-Debug/' . phpversion();
    		mail('bug@linemedia.ru', $message, $full_message, $headers);
    	} catch (Exception $e) {
	    	
    	}
    }
    
    
    /**
     * Добавление видимого админу сообщения при серьёзных ощибках
     */
    public static function addInformer($message, $spoiler, $file = false, $line = false, $priority = false)
    {
    	global $USER;
	    if (!$USER->IsAdmin()) {
	    	return;
        }
	    
	    switch ($priority) {
            case LM_AUTO_DEBUG_USER_ERROR:
                $message = GetMessage('LM_AUTO_DEBUG_USER_ERROR') . ': ' . $message;
                break;
                
            default:
                $message = GetMessage('LM_AUTO_DEBUG_ERROR') . ': ' . $message;
                break;
        }
        
	    $ar = Array(
		   "MESSAGE" => $message,
		   "TAG" => "LM_ERROR",
		   "MODULE_ID" => "linemedia.auto",
		   "ENABLE_CLOSE" => "Y"
		);
		CAdminNotify::Add($ar);
    }
    
    
    /**
     * Запись лога в файл
     */
    public static function setOutputFilename($filename)
    {
	    self::$filename = $filename;
    }
    
}
