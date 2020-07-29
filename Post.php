<?php
require_once 'ActiveRecordEntity.php';
require_once 'Tag.php';

class Post extends ActiveRecordEntity{

    public function tags(){
        return $this->belongsToMany(Tag::class);
    }
}