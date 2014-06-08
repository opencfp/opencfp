#!/usr/bin/env php
<?php

use Doctrine\DBAL\DriverManager;

require __DIR__.'/../vendor/autoload.php';

$options = getopt('v', array('verbose', 'dump-sql'));
$verbose = isset($options['v']) || isset($options['verbose']);
$dumpSql = isset($options['dump-sql']);
$init = isset($options['init']);

$bootstrap = new OpenCFP\Bootstrap();
$app = $bootstrap->getApp();

$migrator = new OpenCFP\Migrator($app['db']);

$sqlStatements = $init ? $migrator->getInitDataQueries() : $migrator->getQueries();

if ($verbose || $dumpSql) {
    foreach ($sqlStatements as $sql) {
        echo $sql."\n";
    }
}

if (!$dumpSql) {
    $migrator->runQueries($sqlStatements);
}

$migrator->runQueries($sqlStatements);
