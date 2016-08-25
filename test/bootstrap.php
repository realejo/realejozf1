<?php
// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'testing'));

// Define application environment
defined('TEST_ROOT')
    || define('TEST_ROOT', realpath(dirname(__FILE__)));

// Define application environment
defined('APPLICATION_DATA')
    || define('APPLICATION_DATA', realpath(dirname(__FILE__) . '/assets/data'));

// Carrega o autoloader do composer
$loader = require_once realpath(dirname(__FILE__) . '/../vendor') . '/autoload.php';

// Carrega os namespaces para teste
$loader->addPsr4("RealejoZf1Test\\", __DIR__ . "/src");

// Carrega o autolader padrão do Zend (Zend_*)
Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);

// Procura pelas configurações do Semaphore
if (isset($_SERVER['DATABASE_MYSQL_USERNAME'])) {
    // Define o banco de dados de testes
    \Zend_Db_Table::setDefaultAdapter(\Zend_Db::factory('mysqli', array(
        'host'           => '127.0.0.1',
        'username'       => $_SERVER['DATABASE_MYSQL_USERNAME'],
        'password'       => $_SERVER['DATABASE_MYSQL_PASSWORD'],
        'dbname'         => 'test',
    )));

    // Procura pelas configurações do Codeship
} elseif (isset($_SERVER['MYSQL_USER'])) {
    // Define o banco de dados de testes
    \Zend_Db_Table::setDefaultAdapter(\Zend_Db::factory('mysqli', array(
        'host'           => '127.0.0.1',
        'username'       => $_SERVER['MYSQL_USER'],
        'password'       => $_SERVER['MYSQL_PASSWORD'],
        'dbname'         => 'test',
    )));

} else {
    // Define o banco de dados de testes
    $config = (file_exists(TEST_ROOT . '/config.db.php')) ? TEST_ROOT .'/config.db.php' : TEST_ROOT .'/config.db.php.dist';
    \Zend_Db_Table::setDefaultAdapter(require $config);
}
