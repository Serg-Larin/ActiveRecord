<?php
require_once 'ActiveRecordEntity.php';
require_once 'Post.php';

class Tag extends ActiveRecordEntity{

    public function posts(){
        return $this->belongsToMany(Post::class);
    }
}