<?php
namespace Core;

class Container {
    private static $instances = [];

    public static function set(string $name, $callable) {
        self::$instances[$name] = $callable;
    }

    public static function get(string $name) {
        if (isset(self::$instances[$name])) {
            $constructor = self::$instances[$name];
            return $constructor();
        }
        return null;
    }
}