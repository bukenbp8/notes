<?php

class Cookie
{
    public static function set($name, $value, $expire)
    {
        if (setcookie($name, $value, time() + $expire, '/')) {
            return true;
        }
        return false;
    }

    public static function delete($name, $expire)
    {
        self::set($name, '', $expire - 100000);
    }

    public static function get($name)
    {
        return $_COOKIE[$name];
    }

    public static function exists($name)
    {
        return isset($_COOKIE[$name]);
    }
}
