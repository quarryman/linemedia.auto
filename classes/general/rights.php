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

class LinemediaAutoRights
{
    const FILE = '.linemedia.access.php';
    
    
    public static function setGroupRights($group_id, $right)
    {
        $rights = self::getRights();
        
        $rights[$group_id] = $right;
        
        self::setRights($rights);
    }
    
    
    /**
     * Получение прав пользователя.
     */
    public function getUserRight($user_id, $module)
    {
        $rights = self::getRights();
        
        $groups = CUser::GetUserGroup($user_id);
        
        $right = 'D';
        foreach ($groups as $group_id) {
            if (array_key_exists($group_id, $rights)) {
                if ($rights[$group_id][$module] > $right) {
                    $right = $rights[$group_id][$module];
                }
            }
        }
        return $right;
    }
    
    
    /**
     * Получение прав.
     */
    protected static function getRights()
    {
        include($_SERVER['DOCUMENT_ROOT'].'/'.self::FILE);
        
        return $rights;
    }
    
    
    /**
     * Установка прав.
     * Принимает массив вида:
     *  array(GROUP_ID => array(MODULE_ID => RIGHT, ...), ...)
     * 
     * @param string $rights
     */
    protected static function setRights($rights)
    {
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/'.self::FILE, '<? $rights = '.var_export($rights, true).' ?>');
    }
    
}
    