<?php

require_once 'ActiveRecordEntity.php';

class Users extends ActiveRecordEntity {


    public function getEmail(){
        return $this->email;
    }

    public static function getTableName(): string
    {
        return 'users';
    }
}