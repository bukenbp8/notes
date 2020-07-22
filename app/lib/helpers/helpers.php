<?php

use Models\Users;

function dnd($data)
{
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    die();
}

function sanitize($dirty)
{
    return htmlentities($dirty, ENT_QUOTES, 'UTF-8');
}

function currentUser()
{
    return Users::currentLoggedInUser();
}

function posted_values($post)
{
    $clean_array = [];
    foreach ($post as $key => $value) {
        $clean_array[$key] = sanitize($value);
    }
    return $clean_array;
}

function makeArray($obj)
{
    return json_decode(json_encode($obj), true);
}
