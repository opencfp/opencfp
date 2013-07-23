<?php
namespace OpenCFP;

use Doctrine\DBAL\DriverManager;

class Migrator
{
    private $conn;

    function __construct(\PDO $db)
    {
        $this->conn = DriverManager::getConnection(array('pdo' => $db));
    }

    function getQueries()
    {
        $current = $this->conn->getSchemaManager()->createSchema();
        $platform = $this->conn->getDatabasePlatform();

        $target = require APP_DIR.'/schema/data.php';

        $sqlStatements = $current->getMigrateToSql($target, $platform);
        return $sqlStatements;
    }

    function runQueries(array $sqlStatements)
    {
        foreach ($sqlStatements as $sql) {
            $this->conn->executeQuery($sql);
        }
    }

    function getInitDataQueries()
    {
        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');
        return array(
            "INSERT INTO groups (id, name, permissions, created_at, updated_at) VALUES (1,'Speakers','{\"users\":1}','$now','$now');",
            "INSERT INTO groups (id, name, permissions, created_at, updated_at) VALUES (2,'Admin','{\"admin\":1}','$now','$now');",
        );
    }

    function migrate()
    {
        $this->runQueries($this->getQueries());
    }

    function init()
    {
        $this->runQueries($this->getInitDataQueries());
    }
}
