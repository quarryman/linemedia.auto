<?php
/**
 * Linemedia Autoportal
 * Main module
 * Connection to Linemedia API
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */


IncludeModuleLangFile(__FILE__); 

class LinemediaAutoApiDriver
{
    const SIG_LENGTH = 8; // Длина подписи.
    
    const DEFAULT_ENCODING = 'UTF-8';
    
    protected $id     = 0; // ID пользователя в системе
    protected $key    = ''; // Ключ пользователя
    
    protected $url    = ''; // Адрес подключения
    protected $format = 'json'; // Формат обмена данными
    
    protected $version = '0.1.0'; // Версия API
    
    
    protected $ignore_modifications = false;
    protected $modifications; // Модификация ответов API
    protected $modifications_set = false; // Смена сета модификаций ответов API
    
    
    /*
    * На случай проблем с DNS в данном, централизованно обновляемом файле, прописан IP сервера
    */
    protected $api_server_ip = '88.198.67.81';
    
    
    /**
     * Конструктор - установка настроек
     */
    public function __construct($id = 0, $key = '', $url = 'api.auto.linemedia.ru', $format = 'json', $stub = '') 
    {
        $this->id       = (int) $id;
        $this->key      = (string) $key;
        $this->url      = (string) $url;
        
        
        /*
         * Если значения не переданы - возьмём стандартные
         */
        if ($this->id < 1) {
        	$this->id = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_API_ID');
        }
        if ($this->key == '') {
        	$this->key = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_API_KEY');
        }
        if ($this->url == '') {
        	$this->url = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_API_URL');
        }
        if ($format == '') {
        	$format = COption::GetOptionString('linemedia.auto', 'LM_AUTO_MAIN_API_FORMAT');
        }
        
        $format = in_array($format, array('json', 'xml', 'serialized')) ? $format : 'json';
        $this->format = (string) $format;
        
        if ($this->format == 'json' && !function_exists('json_encode')) {
            $this->format = 'serialized';
        }
        
        $this->modifications = new LinemediaAutoApiModifications();
        
    }
    
    
    public function __call($function, $args = array())
    {
	    return $this->query($function, $args[0]);
    }
    
    
    /**
     * Выполнение запроса
     */
    public function query($cmd, $data = array())
    {
        /*
		 * Преобразуем массив входных данных в строку нужного формата
		 */
		switch ($this->format) {
			case 'json':
				$encoded_data = json_encode($data);
                break;
			case 'serialized':
				$encoded_data = serialize($data);
                break;
			case 'xml':
				$encoded_data = LinemediaAutoArr2XML::encode($data);
                break;
		}
        
		if (count($data) == 0) {
			$encoded_data = '';
		}
		

		/*
		 * Сгенерируем подпись, удостоверяющую правильность запроса
		 */
		$md5 = md5($cmd . $encoded_data . $this->key);
		$sig = substr($md5, 0, self::SIG_LENGTH);
        
		$encoded_data = urlencode($encoded_data);
        
        
		/*
		 * URL по которому надо отослать запрос
		 */
		$out = $in = $this->format;
		$query = $this->url . "/?cmd=$cmd&data=$encoded_data&sig=$sig&out=$out&in=$in&id=" . $this->id . '&v=' . $this->version;
        
        
		/*
		 * Вывод отладочной информации
		 */
		LinemediaAutoDebug::add('Linemedia API query: ' . $query, print_r($data, true));

		
		/*
		 * Выполнение простого запроса
		 */
        $agent = "Linemedia API Client (" . $_SERVER['SERVER_NAME'] . ") [" . $this->id . "]";
        
        if (function_exists('curl_init')) {
            $ch = curl_init('http://' . $query);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 	1);
            curl_setopt($ch, CURLOPT_HEADER, 			0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 	1);
            curl_setopt($ch, CURLOPT_USERAGENT, 		$uagent);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 	15);
            curl_setopt($ch, CURLOPT_TIMEOUT, 			30);
            curl_setopt($ch, CURLOPT_FAILONERROR, 		1);
            curl_setopt($ch, CURLOPT_AUTOREFERER, 		1);
            
            try {
                $response = curl_exec($ch);
            } catch (Exception $e) {
                throw $e;
            }
            
            $this->last_request = $query;
            
            $error = curl_errno($ch);
            if ($error) {
                throw new Exception (GetMessage('LM_AUTO_MAIN_ERROR_API_REQUEST') . ': ' . curl_error($ch));
            }
            curl_close($ch);
        } else {
            try {
                $response = file_get_contents('http://' . $query);
            } catch (Exception $e) {
                throw $e;
            }
        }
        
		/*
		 * Обработка возможных ошибок
		 * Недоступен сервер API
		 */
		if ($response == '') {
			throw new Exception (GetMessage('LM_AUTO_MAIN_ERROR_API_EMPTY_RESPONSE'), LM_AUTO_DEBUG_ERROR);
			
			$response = array('status' => 'error', 'data' => null, 'error' => array('code' => -1, 'error_text' => 'Получен пустой ответ от сервера'));
			return $response;
		}
        
        
		/*
		 * Преобразуем полученные данные в массив с ответом
		 */
		switch ($this->format) {
			case 'json':
                $response_arr = json_decode($response, 1);
			    break;
			case 'serialized':
				$response_arr = @unserialize($response);
			    break;
			case 'xml':
				$response_arr = LinemediaAutoXML2Arr::decode($response);
				$response_arr = $response_arr['xml'];
			    break;
		}
        
        /*
        *    настройки для перекодировки.
        *    идеальная проверка на опеле в моделях найти "MOVANO B грузовоe".
        */
        mb_substitute_character('');
        setlocale('ru_RU.UTF-8');
        mb_internal_encoding('utf-8');

        /*
         * Преобразование кодировки.
         */
        if (!defined('BX_UTF') || BX_UTF != true) {
            $response_arr = self::iconvArray($response_arr, self::DEFAULT_ENCODING, 'WINDOWS-1251//TRANSLIT');
        }
        
        
		/*
		 * Обработка возможных ошибок
		 * Сервер API вернул неправильный ответ (Сервер API всегда должен возвращать массив)
		 */
		if (!is_array($response_arr)) {
			throw new Exception (GetMessage('LM_AUTO_MAIN_ERROR_API_INCORRECT_RESPONSE') . ' ' . $response, LM_AUTO_DEBUG_ERROR);
		}
        
        
        /*
         * Пришла ошибка.
         */
        if (isset($response_arr['status']) && $response_arr['status'] == 'error') {
        	/*
        	* Пользовательские ошибки
        	*/
        	$user_errors = array(
        		2, // client
        		3, // password
        		112, // period
        		963
        	);
        	if(in_array($response_arr['error']['code'], $user_errors))
	            throw new Exception (GetMessage('LM_AUTO_MAIN_ERROR_API_REQUEST') . ': ' . $response_arr['error']['text'], LM_AUTO_DEBUG_USER_ERROR);
	        else
	        	throw new Exception (GetMessage('LM_AUTO_MAIN_ERROR_API_REQUEST') . ': ' . $response_arr['error']['text'], LM_AUTO_DEBUG_ERROR);
	        
        }
        
        
  		/*
		 * Вывод отладочной информации
		 */
		LinemediaAutoDebug::add('Linemedia API response: ', '<b>' . $cmd . '</b><br>' . print_r($data, true) . print_r($response_arr, true), LM_AUTO_DEBUG_WARNING);
		
		
		/*
		 * Применим модификацию ответов АПИ
		 */
		if (!$this->ignore_modifications) {
			if ($this->modifications_set) {
				$this->modifications->changeSetId($this->modifications_set);
            }
			$this->modifications->applyModifications($cmd, $data, $response_arr['data']);
        }
		
		/*
		 * Выполнение запроса технически завершено успешно
		 */
		return $response_arr;
    }
    
    
    
    /**
     * Отключим модификацию результата
     */
    public function ignoreModifications()
    {
	    $this->ignore_modifications = true;
    }
    
    
    /**
     * Изменим сет модификации результата
     */
    public function changeModificationsSetId($id)
    {
	    $this->modifications_set = $id;
    }
    
    
    /**
     * Конвертация пришедшего из текдока массива
     */
    protected function iconvArray($array, $from = 'UTF-8', $to = 'cp1251')
    {
        if (empty($array) || !is_array($array)) {
            return array();
        }
        
        $result = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result[$key] = self::iconvArray($value, $from, $to);
            } else {
                $result[$key] = iconv($from, $to, $value);
            }
        }
        return $result;
    }
    
}


