<?php
require_once 'Db.php';
require_once 'ActiveRecordEntity.php';

class Posts extends ActiveRecordEntity {


    public static function getTableName(): string
    {
        return self::$tableName??'posts';
    }
}

