<?php
require_once 'Db.php';
require_once 'ActiveRecordEntity.php';

class Tasks extends ActiveRecordEntity {


    public static function getTableName(): string
    {
        return 'tasks';
    }
}

