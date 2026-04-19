<?php
namespace Core\Database;

use PDO;
use Exception;

class Connection {
    private static $instance = null;

    public static function getInstance() {
        if (self::$instance === null) {
            $conf = require dirname(__DIR__, 2) . '/config/database.php';
            try {
                $dsn = "mysql:host={$conf['host']};dbname={$conf['database']};charset={$conf['charset']}";
                self::$instance = new PDO($dsn, $conf['username'], $conf['password'], $conf['options']);
            } catch (Exception $e) {
                error_log("DATABASE ERROR: " . $e->getMessage());
                die("<h2 style='color:red;font-family:sans-serif;'>Liaison BDD interrompue.</h2>");
            }
        }
        return self::$instance;
    }
}