<?php

/**
 * This class is used for helper functions
 *
 * @package System\Api\Generator
 * @author gc@techbanx.com
 * @version 1.0.0
 */
namespace System\Api\Generator;

use Phalcon\Config\Adapter\Php;
use Phalcon\Db;
use Phalcon\Di;

abstract class Utils
{

    /**
     * Converts the underscore_notation to the UpperCamelCase
     *
     * @param string $string String to camelize
     * @return string
     */
    public static function camelize($string) {
        $stringParts = explode('_', $string);
        $stringParts = array_map('ucfirst', $stringParts);

        return implode('', $stringParts);
    }

    /**
     * Converts the underscore_notation to the lowerCamelCase
     *
     * @param string $string String to lowerCamelize
     * @return string
     */
    public static function lowerCamelize($string) {
        return lcfirst(self::camelize($string));
    }

    /**
     * This method returns the database connexion from the DI
     *
     * @param $db String Name of the database in the Lms
     * @return object Database Connexion
     */
    public static function getDb($db) {
        return Di::getDefault()->get("db_$db");
    }

    /**
     * This method returns the Initials of the User which is currently
     * authenticated
     *
     * @return string Uppercase Initials of the Auth User
     */
    public static function getInitialAuthUser() {
        return strtoupper(explode('@',Di::getDefault()->getAuth()->user['email'])[0]);
    }

    /**
     * This method cleans a folder and destroy if necessary
     *
     * @param  string $dirName String Folder to clean
     * @param  bool $destroyFolder option to destroy the Folder
     * @return bool True if the operation has been done successfully
     */
    public static function cleanFolder($dirName, $destroyFolder = false) {
        array_map('unlink', glob("$dirName/*.*"));
        if($destroyFolder) {
            rmdir($dirName);
        }
        return true;
    }

    /**
     * This method saves a file with the content inside
     *
     * @param  string $pathFile String Path of the File to save
     * @param  string $contentFile String Content of the File
     * @return bool True if the file has been saved successfully
     */
    public static function saveFile($pathFile, $contentFile) {
        // Check if directory exits
        if(!is_dir(dirname($pathFile))) {
            mkdir(dirname($pathFile), 0777);
        }
        // Creation of the file and insertion of the code into it
        if (!file_put_contents($pathFile, $contentFile)) {
            return false;
        }
        chmod($pathFile, 0777);
        return true;
    }

    /**
     * The purpose of this method is to return the configuration file
     * in the App/Storage/Db Folder as an array
     *
     * @return mixed Config File of Storage
     */
    public static function getStorageConfig() {
        return new Php(DIR['application'].'/Storage/Db/config.php');
    }

    /**
     * This method returns the config file for a specific database
     *
     * @param  string $type Type of Storage (Model or Collection)
     * @param  string $db Name of the database in the Lms
     * @return mixed Config File of the database From Storage/Db/Config.php
     */
    public static function getDatabaseConfig($type, $db) {
        $config = self::getStorageConfig();
        return $config[$type][ucfirst($db)];
    }

    /**
     * This methods returns the list of all 'url' folder generated
     * from the Storage file config (Config.php)
     *
     * @return array List of Path 'Generated' from different databases
     */
    public static function getStoragePathGeneratedList() {
        $config = self::getStorageConfig();
        $listPath = [];
        foreach($config as $dbType) {
            foreach($dbType as $dbConnection) {
                $listPath [] = DIR['application'].$dbConnection['pathGenerated'];
            }
        }
        return $listPath;
    }

    /**
     * This method return the list of Databases used in the application
     * and which are registered in the config file
     *
     * @return array List of databases used in the Application
     */
    public static function getDatabasesList() {
        $config = new Php(DIR['application'].'/Storage/Db/config.php');
        $dbList = [];
        foreach (['Model', 'Collection'] as $type) {
            foreach ($config[$type]->toArray() as $row) {
                $db = ['type' => $type, 'name' => $row['dbConnectionName']];
                $dbList [] = $db;
            }
        }
        return $dbList;
    }

    /**
     * This method returns the name of the database in the DBMS
     *
     * @param  string $type Type of Storage (Model or Collection)
     * @param  string $db Name of the database in the Lms
     * @return string Name of the database
     */
    public static function getDatabaseName($type, $db) {
        if($type == 'Model') {
            return self::getDb($db)->getDescriptor()['dbname'];
        } else {
            return self::getDb($db)->getDatabaseName();
        }
    }

    /**
     * This method returns the list of tables from a database
     *
     * @param  string $db Name of the database in the Lms
     * @param  bool $allTable Add 'all' to list of Tables
     * @return array list of tables
     */
    public static function getDatabaseTables($db, $allTable = false) {
        $dbName = self::getDatabaseName('Model', $db);

        // UNION ALL in the query is a trick to add 'All' name in the list
        $sql  = $allTable ? "SELECT 'All' as name, 'Table/View' as type UNION ALL " : ""
              . "SELECT TABLE_NAME as name,                                 "
              . "       IF(TABLE_TYPE like 'VIEW', 'View', 'Table') as type "
              . "FROM   information_schema.tables                           "
              . "WHERE  TABLE_SCHEMA = '$dbName'                            ";

        $tablesList = self::getDb($db)->fetchAll($sql, Db::FETCH_OBJ);
        return $tablesList;
    }

    /**
     * This methods returns the list of a collections located in
     * a Database (Mongo)
     *
     * @param string $db Name of the database in the Lms
     * @param bool $allCollection  Add 'all' to list of Collections
     * @return array List of the collection in the database passed as a param
     */
    public static function getCollections($db, $allCollection = false) {
        $collections = self::getDb($db)->listCollections();
        $list = [];
        foreach($collections as $collection) {
            $list[] = $collection->getName();
        }
        if($allCollection) {
            array_unshift($list, 'All');
        }
        return $list;
    }

    /**
     * This method returns true if the table mapped with the current model
     * has a "deleted" field
     *
     * @param string $db Name of the database in the Lms
     * @param string $tableName Name of table in the database
     * @return bool does the Table has delete field ?
     */
    public static function isTableSoftDelete($db, $tableName) {
        $dbName = self::getDatabaseName('Model', $db);

        $sql = "SELECT count(*) as has_delete_field "
             . "FROM information_schema.columns     "
             . "WHERE TABLE_NAME   = '$tableName'   "
             . "  AND TABLE_SCHEMA = '$dbName'      "
             . "  AND COLUMN_NAME  = 'deleted'      ";

        $result = self::getDb($db)->fetchAll($sql, Db::FETCH_OBJ);
        return ($result[0]->has_delete_field == 1);
    }


    /**
     * The purpose of this method is to get the list of fields
     * of the table mapped with the current model.
     * This fields ( or columns) will become attributes in the model generated
     *
     * @param string $db Name of the database in the Lms
     * @param string $tableName Name of table in the database
     * @return array List of all the fields
     */
    public static function getTableFields($db, $tableName) {
        $dbName = self::getDatabaseName('Model', $db);

        $sql = "SELECT *, IF(EXTRA like 'VIRTUAL%', 'YS', 'NO') as IS_VIRTUAL "
             . "FROM information_schema.columns                               "
             . "WHERE TABLE_NAME   = '$tableName'                             "
             . "  AND TABLE_SCHEMA = '$dbName'                                "
             . "ORDER BY ORDINAL_POSITION                                     ";

        $arrayAttributes = self::getDb($db)->fetchAll($sql, Db::FETCH_OBJ);
        return $arrayAttributes;
    }


    /**
     * This method returns the list of all the virtual columns
     * of the table passed as a parameter
     *
     * @param string $db Name of the database in the Lms
     * @param string $tableName Name of table in the database
     * @return array List of all the virtual attributes
     */
    public static function getTableVirtualFields($db, $tableName) {
        $dbName = self::getDatabaseName('Model', $db);
        $listAttributes = [];

        $sql = "SELECT COLUMN_NAME                "
             . "FROM information_schema.columns   "
             . "WHERE TABLE_NAME   = '$tableName' "
             . "  AND TABLE_SCHEMA = '$dbName'    "
             . "  AND EXTRA like 'VIRTUAL%'       ";

        $result = self::getDb($db)->fetchAll($sql, Db::FETCH_OBJ);
        foreach ($result as $item) {
            $listAttributes[] = $item->COLUMN_NAME;
        }
        return $listAttributes;
    }


    /**
     * This method returns the datetime of the last alter table
     *
     * @param string $db Name of the database in the Lms
     * @param string $tableName Name of table in the database
     * @return string Date of the last alter table
     */
    public static function getTableVersionDate($db, $tableName) {
        $dbName = self::getDatabaseName('Model', $db);

        $sql = "SELECT UPDATE_TIME                 "
             . "FROM   information_schema.tables   "
             . "WHERE TABLE_SCHEMA = '$dbName'     "
             . "  AND TABLE_NAME    = '$tableName' ";

        $result = self::getDb($db)->fetchAll($sql, Db::FETCH_OBJ);
        return $result[0]->UPDATE_TIME ?: "New in the Database";
    }

    /**
     * This methods return the type of a table. It means is it a View
     * or a Table ?
     *
     * @param string $db Name of the database in the Lms
     * @param string $tableName Name of table in the database
     * @return string Type of the table ('View' or 'Table')
     */
    public static function getTableType($db, $tableName) {
        $dbName = self::getDatabaseName('Model', $db);

        $sql = "SELECT IF(TABLE_TYPE like 'VIEW', 'View', 'Table') as type "
             . "FROM   information_schema.tables                           "
             . "WHERE TABLE_SCHEMA  = '$dbName'                            "
             . "  AND TABLE_NAME    = '$tableName'                         ";

        $result = self::getDb($db)->fetchAll($sql, Db::FETCH_OBJ);
        return $result[0]->type;
    }
}