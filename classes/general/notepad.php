<?php

/**
 * Linemedia Autoportal
 * Main module
 * Parts search class
 *
 * @author  Linemedia
 * @since   07/03/2012
 *
 * @link    http://auto.linemedia.ru/
 */

IncludeModuleLangFile(__FILE__);

class LinemediaAutoNotepad
{
    var $LAST_ERROR = "";
    const TABLE = 'b_lm_notepad';


    /**
     * Список запчастей.
     */
    public static function getList($aSort = array(), $aFilter = array())
    {
        global $DB;

        $arFilter = array();
        foreach ($aFilter as $key => $val) {
            if (strlen($val) <= 0) {
                continue;
            }
            switch ($key) {
                case "id":
                case "user_id":
                case "auto_id":
                case "quantity":
                    $arFilter []= "N.".$key." = '".$DB->ForSql($val)."'";
                    break;
                case "title":
                case "article":
                case "brand_title":
                case "auto":
                case "notes":
                case "added":
                    $arFilter []= "N.".$key." LIKE '%".$DB->ForSql($val)."%'";
                    break;
            }
        }

        $arOrder = array();
        foreach ($aSort as $key => $val) {
            $ord = (strtoupper($val) <> "ASC" ? "DESC" : "ASC");

            switch ($key) {
                case "id":
                case "user_id":
                case "title":
                case "article":
                case "brand_title":
                case "auto":
                case "auto_id":
                case "quantity":
                case "notes":
                case "added":
                    $arOrder []= "N.".$key." ".$ord;
                    break;
            }
        }
        if (count($arOrder) == 0) {
            $arOrder[] = "N.id DESC";
        }
        $sOrder = "\nORDER BY ".implode(", ", $arOrder);

        if (count($arFilter) == 0) {
            $sFilter = "";
        } else {
            $sFilter = "\nWHERE ".implode("\nAND ", $arFilter);
        }

        $strSql = "
            SELECT
                N.*
            FROM
                `".self::TABLE."` N
            ".$sFilter.$sOrder;

        return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
    }

    /**
     * Получение элемента по ID.
     */
    public static function getById($id)
    {
        global $DB;
        $id = intval($id);

        $strSql = "
            SELECT
                N.*
            FROM `".self::TABLE."` N
            WHERE N.id = ".$id."
        ";

        return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
    }
    /**
     * Получение записей пользователя с заданным ID.
     */
    public static function getByUserId($user_id)
    {
        global $DB;
        $user_id = intval($user_id);

        $strSql = "
            SELECT
                N.*
            FROM `".self::TABLE."` N
            WHERE N.user_id = ".$user_id."
        ";

        return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
    }


    /**
     * Удаление по ID записи в таблице.
     */
    public static function deleteById($id)
    {
        global $DB;
        $id = intval($id);

        $DB->StartTransaction();

        $strSql = "DELETE FROM `".self::TABLE."` WHERE `id` = $id";
        $res = $DB->Query(
            $strSql,
            false,
            "File: ".__FILE__."<br>Line: ".__LINE__
        );

        if ($res) {
            $DB->Commit();
        } else {
            $DB->Rollback();
        }

        return $res;
    }


    //check fields before writing
    public function checkFields($arFields)
    {
        $this->LAST_ERROR = "";
        $aMsg = array();

        if (!isset($arFields["user_id"]) || strlen($arFields["user_id"]) == 0) {
            $aMsg[] = array("id" => "user_id", "text" => GetMessage("class_rub_err_user_id"));
        }

        if (!isset($arFields["article"]) || strlen($arFields["article"]) == 0) {
            $aMsg[] = array("id" => "article", "text" => GetMessage("class_rub_err_article"));
        }

        if (!empty($aMsg)) {
            $e = new CAdminException($aMsg);
            $GLOBALS["APPLICATION"]->ThrowException($e);
            $this->LAST_ERROR = $e->GetString();
            return false;
        }
        return true;
    }



    /**
     * Добавление.
     */
    public function add($arFields)
    {
        global $DB;

        if (!$this->CheckFields($arFields)) {
            return false;
        }
        $id = $DB->Add(self::TABLE, $arFields);
        return $id;
    }


    /**
     * Обновление.
     */
    public function update($id, $arFields)
    {
        global $DB;
        $id = intval($id);

        $strUpdate = $DB->PrepareUpdate(self::TABLE, $arFields);
        if ($strUpdate != "") {
            $strSql = "UPDATE `".self::TABLE."` SET ".$strUpdate." WHERE `id` = ".$id;
            $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
        }
        return true;
    }
}
