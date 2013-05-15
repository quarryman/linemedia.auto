<?php

/**
 * Linemedia Autoportal
 * Main module
 * Basket management class
 *
 * @author  Linemedia
 * @since   22/01/2012
 *
 * @link    http://auto.linemedia.ru/
 */
 
IncludeModuleLangFile(__FILE__); 
 
/*
 * ����� ��� �����������.
 */
class LinemediaAutoLogger
{
    const PATH_TO_LOG = '/upload/linemedia.auto/logs/';
    
    protected $code = null; // ��� ����, ������������ ��� �������� �����.
    protected $path = null; // ���� � ����� ����.
    
    /**
     * �����������
     * 
     * @param string $code
     */
    public function __construct($code, $path = null)
    {
        $this->code = (string) $code;
        $this->path = (!empty($path)) ? (strval($path)) : (self::PATH_TO_LOG);
    }
    
    
    /**
     * ������ � ���.
     * 
     * @param string $message
     */
    public function write($message)
    {
        $log  = date('d.m.Y H:i:s') . ":\n";
        $log .= 'BX user: ' . (string) ((CUser::GetID() > 0) ? (CUser::GetID()) : ('no')) . "\n";
        $log .= 'Page: ' . $_SERVER['REQUEST_URI'] . "\n";
        $log .= $message . "\n\n\n";
        
        file_put_contents($_SERVER['DOCUMENT_ROOT'].$this->path.$this->code.'.log', $log, FILE_APPEND);
    }
    
    
    /**
     * ������� ����.
     */
    public function clear()
    {
        file_put_contents($_SERVER['DOCUMENT_ROOT'].self::PATH_TO_LOG.$this->code.'.log', '');
    }
    
    
    /**
     * �������� ����� ����.
     */
    public function delete()
    {
        @unlink($_SERVER['DOCUMENT_ROOT'].self::PATH_TO_LOG.$this->code.'.log');
    }
}

