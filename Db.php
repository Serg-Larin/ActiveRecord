<?php

require_once 'config.php';

class Db
{

    private $connect;

    private static $instance;

    private function __construct()
    {
        $this->connect = new \PDO(
            'mysql:host=localhost;dbname=testovoe',
            'root','');

        $this->connect->exec('SET NAMES UTF8');
    }
    public function getLastInsertId(): int
    {
        return (int) $this->connect->lastInsertId();
    }

    public function query(string $sql, $params = [], $className = 'stdClass'): ?array
    {
        $sth = $this->connect->prepare($sql);
        $result = $sth->execute($params);

        if (false === $result) {
            return null;
        }

        return $sth->fetchAll(\PDO::FETCH_CLASS, $className);
    }
    public static function getInstance(): self
    {
        self::$instance;
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
