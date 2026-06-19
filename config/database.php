<?php
// config/database.php

define('MYSQL_HOST', 'localhost');
define('MYSQL_DB', 'ecommerce_oltp');
define('MYSQL_USER', 'root');
define('MYSQL_PASS', '');

define('MSSQL_SERVER', 'localhost\\SQLEXPRESS'); 
define('MSSQL_DB', 'ecommerce_olap');
define('MSSQL_USER', 'sa');
define('MSSQL_PASS', 'YourSecurePassword123'); // UPDATE THIS to match your local MS SQL system password!

class DatabaseConnection {
    private static $mysqlPdo = null;
    private static $mssqlPdo = null;

    // Secure PDO connection handling matching Module 17 instructions
    public static function getMySQL() {
        if (self::$mysqlPdo === null) {
            try {
                $dsn = "mysql:host=" . MYSQL_HOST . ";dbname=" . MYSQL_DB . ";charset=utf8mb4";
                self::$mysqlPdo = new PDO($dsn, MYSQL_USER, MYSQL_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Fetch as associative arrays
                    PDO::ATTR_EMULATE_PREPARES => false // Enforce raw native prepared statements
                ]);
            } catch (PDOException $e) {
                error_log("MySQL Connection Failure: " . $e->getMessage()); // Securely log error internally
                die("Critical framework infrastructure failure encountered."); // Mask raw database errors from the user
            }
        }
        return self::$mysqlPdo;
    }

    public static function getMSSQL() {
        if (self::$mssqlPdo === null) {
            try {
                // Connection using the official Microsoft SQL Server Driver for PHP
                $dsn = "sqlsrv:Server=" . MSSQL_SERVER . ";Database=" . MSSQL_DB;
                self::$mssqlPdo = new PDO($dsn, MSSQL_USER, MSSQL_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
            } catch (PDOException $e) {
                error_log("MS SQL Server Connection Failure: " . $e->getMessage());
                // Non-blocking catch: returns null if analytical warehouse is offline, keeping the customer storefront operational
                return null;
            }
        }
        return self::$mssqlPdo;
    }
}