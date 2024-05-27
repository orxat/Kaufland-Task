<?php

class Database
{
    private $pdo;
    private $config;
    private $dbType;

    public function __construct($config)
    {
        $this->config = $config;
        $this->dbType = $this->config->get('database', 'type');
        if ($this->dbType === 'mysql') { // I'm more used to MySQL so for it we need to create the Database, unlike in SQLite wich will do it in the connect()
            $this->initializeDatabase();
        }
        $this->connect();
        $this->initializeTable();
    }

    private function initializeDatabase()
    {
        $dsn = "mysql:host={$this->config->get('database', 'host')}";
        try {
            $pdo = new PDO($dsn, $this->config->get('database', 'root_username'), $this->config->get('database', 'root_password'));
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $dbname = $this->config->get('database', 'dbname');
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (PDOException $e) {
            $this->logError("Database creation failed: " . $e->getMessage());
            exit("Database creation failed. Check log for details.");
        }
    }

    private function connect()
    {
        try {
            if ($this->dbType === 'mysql') {
                $dsn = "mysql:host={$this->config->get('database', 'host')};dbname={$this->config->get('database', 'dbname')}";
                $username = $this->config->get('database', 'username');
                $password = $this->config->get('database', 'password');
            } elseif ($this->dbType === 'sqlite') {
                $dsn = "sqlite:" . $this->config->get('database', 'path');
                $username = null;
                $password = null;
            } else {
                throw new Exception("Unsupported database type: " . $this->dbType);
            }

            $this->pdo = new PDO($dsn, $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $this->logError("DB Connection failed: " . $e->getMessage());
            throw new Exception("DB Connection failed. Check log for details.");
        } catch (Exception $e) {
            $this->logError("Unsupported database type: " . $this->dbType);
            throw new Exception("Unsupported database type. Check log for details.");
        }
    }

    private function initializeTable()
    {
        if ($this->dbType === 'mysql') {
            $sql = "
                CREATE TABLE IF NOT EXISTS items (
                    entity_id INT PRIMARY KEY,
                    category VARCHAR(255),
                    sku VARCHAR(255),
                    name VARCHAR(255),
                    description TEXT,
                    shortdesc TEXT,
                    price DOUBLE,
                    link TEXT,
                    image TEXT,
                    brand VARCHAR(255),
                    rating INT,
                    caffeine_type VARCHAR(255),
                    count INT,
                    flavored VARCHAR(255),
                    seasonal VARCHAR(255),
                    instock VARCHAR(255),
                    facebook INT,
                    iskcup INT
                );
            ";
        } elseif ($this->dbType === 'sqlite') {
            $sql = "
                CREATE TABLE IF NOT EXISTS items (
                    entity_id INTEGER PRIMARY KEY,
                    category TEXT,
                    sku TEXT,
                    name TEXT,
                    description TEXT,
                    shortdesc TEXT,
                    price REAL,
                    link TEXT,
                    image TEXT,
                    brand TEXT,
                    rating INTEGER,
                    caffeine_type TEXT,
                    count INTEGER,
                    flavored TEXT,
                    seasonal TEXT,
                    instock TEXT,
                    facebook INTEGER,
                    iskcup INTEGER
                );
            ";
        }

        $this->pdo->exec($sql);
    }

    public function insertItem($item)
    {
        if ($this->dbType === 'mysql') {
            $sql = "
                INSERT INTO items (entity_id, category, sku, name, description, shortdesc, price, link, image, brand, rating, caffeine_type, count, flavored, seasonal, instock, facebook, iskcup)
                VALUES (:entity_id, :category, :sku, :name, :description, :shortdesc, :price, :link, :image, :brand, :rating, :caffeine_type, :count, :flavored, :seasonal, :instock, :facebook, :iskcup)
                ON DUPLICATE KEY UPDATE
                    category = VALUES(category),
                    sku = VALUES(sku),
                    name = VALUES(name),
                    description = VALUES(description),
                    shortdesc = VALUES(shortdesc),
                    price = VALUES(price),
                    link = VALUES(link),
                    image = VALUES(image),
                    brand = VALUES(brand),
                    rating = VALUES(rating),
                    caffeine_type = VALUES(caffeine_type),
                    count = VALUES(count),
                    flavored = VALUES(flavored),
                    seasonal = VALUES(seasonal),
                    instock = VALUES(instock),
                    facebook = VALUES(facebook),
                    iskcup = VALUES(iskcup);
            ";
        } elseif ($this->dbType === 'sqlite') {
            $sql = "
                INSERT INTO items (entity_id, category, sku, name, description, shortdesc, price, link, image, brand, rating, caffeine_type, count, flavored, seasonal, instock, facebook, iskcup)
                VALUES (:entity_id, :category, :sku, :name, :description, :shortdesc, :price, :link, :image, :brand, :rating, :caffeine_type, :count, :flavored, :seasonal, :instock, :facebook, :iskcup)
                ON CONFLICT(entity_id) DO UPDATE SET
                    category = excluded.category,
                    sku = excluded.sku,
                    name = excluded.name,
                    description = excluded.description,
                    shortdesc = excluded.shortdesc,
                    price = excluded.price,
                    link = excluded.link,
                    image = excluded.image,
                    brand = excluded.brand,
                    rating = excluded.rating,
                    caffeine_type = excluded.caffeine_type,
                    count = excluded.count,
                    flavored = excluded.flavored,
                    seasonal = excluded.seasonal,
                    instock = excluded.instock,
                    facebook = excluded.facebook,
                    iskcup = excluded.iskcup;
            ";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($item);
    }

    private function logError($message)
    {
        global $logger;
        if ($logger) {
            $logger->log($message);
        } else {
            echo $message;
        }
    }
}
