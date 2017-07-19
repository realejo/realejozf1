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
$loader = require realpath(dirname(__FILE__) . '/../vendor') . '/autoload.php';

// Gambiarra para funcionar o PHPUnit 6.2
class_alias('\PHPUnit\Framework\TestCase', '\PHPUnit_Framework_TestCase');
class_alias('\PHPUnit\Framework\Error\Notice', '\PHPUnit_Framework_Error_Notice');

// Procura pelas configurações do Semaphore CI
if (isset($_SERVER['DATABASE_MYSQL_USERNAME'])) {
    // Define o banco de dados de testes
    \Zend_Db_Table::setDefaultAdapter(\Zend_Db::factory('mysqli', array(
        'host'           => '127.0.0.1',
        'username'       => $_SERVER['DATABASE_MYSQL_USERNAME'],
        'password'       => $_SERVER['DATABASE_MYSQL_PASSWORD'],
        'dbname'         => 'test',
    )));

} else {
    // Define o banco de dados de testes
    $config = (file_exists(TEST_ROOT . '/config.db.php')) ? TEST_ROOT .'/config.db.php' : TEST_ROOT .'/config.db.php.dist';
    \Zend_Db_Table::setDefaultAdapter(require $config);
}
