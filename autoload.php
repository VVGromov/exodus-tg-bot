<?php
class __autoload {
    static function includeClass($className) {
        if (strpos($className, 'app') !== false) {
            $className = str_replace(" ", "", str_replace("/", "\\", str_replace('_', '-', str_replace('/ ', '/', str_replace('/', '/ ', $className)))));
            $className = str_replace("\\", "/", $className);
            include_once(dirname(__FILE__) . '/../' . $className . '.php');
        }
    }
}

spl_autoload_register('__autoload::includeClass');
