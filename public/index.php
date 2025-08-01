<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpTestTask\Search;

define('CONFIG_FILE', __DIR__ . '/../src/database.ini');

try {
    
    $pdo = getPDOConnection();
    $search = new Search($pdo);

    $searchTerm = $_GET['search'] ?? '';
    $results = [];
    $error = '';

    if (!empty($searchTerm)) {
        try {
            $results = $search->searchComments($searchTerm);
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }

    include __DIR__ . '/../templates/search.phtml';
} catch (PDOException $e) {
    die("Ошибка подключения к БД: " . $e->getMessage());
}

// Подключаемся к бд, параметры для подключения получаем из файла /src/database.ini
function getPDOConnection() 
{
    try {
        $params = parse_ini_file(CONFIG_FILE);
    } catch (PDOException $e) {
        throw new \Exception("Файл {CONFIG_FILE} не найден.");
    }
    
    if ($params === false) {
        throw new \Exception("Невозможно причитать файл с конфигурацией базы данных.");
    }
    $conStr = sprintf(
        "pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s",
        $params['host'],
        $params['port'],
        $params['database'],
        $params['user'],
        $params['password']
    );
    try {
        $pdo = new \PDO($conStr);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        throw new \Exception("Ошибка подключения к базе данных: " . $e->getMessage());
    }
}