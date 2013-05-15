<?php

/**
 * Класс вывода данных в формате xls.
 */
class LinemediaAutoExcel
{
    const TYPE_INT      = 'int';
    const TYPE_DECIMAL  = 'decimal';
    const TYPE_TEXT     = 'text';
    const TYPE_DATE     = 'date';
    
    protected $headers  = array();
    protected $notes    = array();
    protected $rows     = array();
    protected $types    = array();
    
    
    /**
     * Добавление заголовков.
     */
    public function addHeader($headers)
    {
        $this->headers[] = (array) $headers;
    }
    
    
    /**
     * Добавление сообщений.
     */
    public function addNote($note)
    {
        $this->notes[] = (string) $note;
    }
    
    
    /**
     * Добавление типов столбцов.
     */
    public function addColumnTypes($types)
    {
        $this->types = (array) $types;
    }
    
    
    /**
     * Добавление строки.
     */
    public function addRow($row)
    {
        $this->rows[] = (array) $row;
    }
    
    
    /**
     * Формирование html для сохранения.
     */
    function getResult()
    {
	    $out = '
	    <html>
	    <head>
	    <title></title>
	    <meta http-equiv="Content-Type" content="text/html; charset='.LANG_CHARSET.'">
	    <style>
            td.int {mso-number-format:"0";}
            td.decimal {mso-number-format:"0.00";}
            td.text {mso-number-format:"@";}
            td.date {mso-number-format:"dd.mm.yyyy";}
        
		    .number0 {mso-number-format:0;}
		    .number2 {mso-number-format:Fixed;}
	    </style>
	    </head>
	    <body>';
        
	    $out .= "<table border=\"1\">";
	    
        /*
         * Сообщения.
         */
        foreach ($this->notes as $note) {
            $out .= '<tr><th colspan="'.count(reset($this->headers)).'">' . $note . '</th></tr>';
        }
        
	    /*
	     * Заголовки.
	     */
	    foreach ($this->headers as $row) {
		    $out .= "<tr>";
		    foreach ($row as $item) {
			    $out .= '<th>' . $item . '</th>';
		    }
		    $out .= "</tr>";
	    }
	    
	    /*
	     * Строки.
	     */
	    foreach ($this->rows as $row) {
	        $out .= '<tr>';
    	    foreach ($row as $i => $item) {
                $type = '';
                if ($this->types[$i] && in_array($this->types[$i], self::getTypes())) {
                    $type = $this->types[$i];
                }
                $out .= '<td class="'.$type.'">'.$item.'</td>';
            }
            $out .= '</tr>';
        }

	    $out .= "</table>";
	    $out .= '</body></html>';
	    
	    return $out;
    }
    
    
    public static function getTypes()
    {
        $types = array(
            self::TYPE_INT,
            self::TYPE_DECIMAL,
            self::TYPE_TEXT,
            self::TYPE_DATE
        );
        return $types;
    }
    
}
