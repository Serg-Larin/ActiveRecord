<?php
function stdOut($data){
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}
require_once 'Post.php';
require_once 'Tag.php';

$obj = Tag::getById(2);

stdOut($obj->posts());