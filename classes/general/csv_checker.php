<?php


/**
 * Linemedia Autoportal
 * Main module
 * CSV file checker class
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__);



/**
 * Класс, отвечающий за проверку валидности CSV файла прайслиста
 * 
 *			CModule::IncludeModule('linemedia.auto');
 *			$a = new LinemediaAutoCSVChecker;
 *			$a->checkFile(
 *			'/home/bitrix/www/upload/linemedia.autodownloader/converting/3_12323_FORUM-AUTO_PRICE (2012-06-13 08-10).xls.csv.result', ',', 0);
 *
 */
class LinemediaAutoCSVChecker
{
	/*
	 * Какие поля и под каким номером должны присутствовать в CSV
	 */
	protected $fields = array(
		'brand_title'    => array(
			'index' => 0,
			'min_length' => 0,
		),
        'article'        => array(
			'index' => 1,
			'min_length' => 1,
		),
        'title'          => array(
			'index' => 2,
			'min_length' => 0,
		),
        'price'          => array(
			'index' => 3,
			'min_length' => 1,
			'regexp' => '#^[0-9.]{1,}$#',
		),
        'quantity'       => array(
			'index' => 4,
			'min_length' => 1,
			'regexp' => '#^[0-9.]{1,}$#',
		),
        'bulk'           => array( // некогда group_id
			'index' => 5,
			'min_length' => 0,
			'optional' => true,
		),
        'weight'         => array(
			'index' => 6,
			'min_length' => 0,
			'regexp' => '#^[0-9.]{1,}$#',
			'optional' => true,
		),
	);
	
	protected $parsed_data = array(
		'string' => '',
		'array' => array()
	);
    
    protected $lmfields = null;
	
    
    
    
    public function __construct()
    {
        $this->lmfields = new LinemediaAutoCustomFields();
    }
    
    
    /**
     * Проверка файла.
     * 
     * @param string $filename
     * @param int $skip_lines
     */
    public function checkFile($filename, $skip_lines = 3)
    {
    
    	$separator = ';';
        
    	
    	/*
    	 * Существует ли файл
    	 */
		if (!file_exists($filename)) {
			throw new Exception ('File not found');
        }
        
    	/*
    	 * Размер файла
    	 */
		if (filesize($filename) == 0) {
			throw new Exception ('Zero filesize');
        }
        
		/*
		 * Читаемый ли файл
		 */
		if (!is_readable($filename)) {
			throw new Exception ('Not readable');
        }
		
		/*
		 * Откроем файл
		 */
		$handle = fopen($filename, "r");
		
		/*
         * Пропустим пару строк?
         */
        for ($i = 0; $i < $skip_lines; $i++) {
	        $data = fgetcsv($handle, 4000, $separator);
        }
		
		
		/*
		 * Получим данные для анализа 
		 * Строку и массив
		 */
		$f_start = ftell($handle);
		$array = fgetcsv($handle, 4000, $separator);
		$f_end = ftell($handle);
		$f_diff = $f_end - $f_start;
		fseek($fp, -$f_diff);
		$string = fread($handle, $f_diff);
		
		$this->parsed_data['string'] = $string;
		foreach ($this->fields as $fieldname => $data) {
			$value = $array[$data['index']];
			$this->parsed_data['array'][$fieldname] = $value;
		}
		
		$colums_number_needed = 0;
		foreach ($this->fields as $fieldname => $data) {
			if ($data['optional'] == true) {
				continue;
            }
			$colums_number_needed++;
		}
		
        /*
         * Добавим пользовательские поля.
         */
        $custom_fields = $this->lmfields->getFields();
        
        $colums_number_needed += count($custom_fields);
        
        
		/*
		 * Поверка разделителя
		 */
		$separator_count = substr_count($string, $separator);
		$separator_needed_count = $colums_number_needed - 1;
        
		if ($separator_count < $separator_needed_count) {
			throw new Exception('Field separator appears ' . $separator_count . ' times instead of minimum ' . $separator_needed_count);
        }
		
		/*
		 * Проверим количество колонок
		 */
		if (count($array) < $colums_number_needed) {
			throw new Exception('Incorrect columns count ('.count($array).'), should be ' . $colums_number_needed);
        }
        
		/*
	 	 * Проверим каждую колонку
		 */
		foreach ($this->fields as $fieldname => $data) {
			$value = $array[$data['index']];
			if ($fieldname == 'bulk') {
				if ($data[$index] != '') {
					throw new Exception('Bulk empty column is not really empty');
                }
			} else {
				if ($data['optional'] == true) {
					continue;
                }
                
				if (strlen(trim($value)) < $data['min_length']) {
					throw new Exception('Column ' . $fieldname . ' is empty or less then ' . $data['min_length'] . ' characters');
                }
                
                if (isset($data['regexp'])) {
                    if (!preg_match($data['regexp'], $value)) {
                       throw new Exception('Column ' . $fieldname . ' doesn\'t match regexp ' . $data['regexp']);
                    }
                }
			}
		}
		
		/*
		 * Все проверки пройдены успешно
		 */
		return true;		
    }
    
    
    /**
     * Получение данных.
     */
    public function getParsedData()
    {
	    return $this->parsed_data; 
    }
    
}

