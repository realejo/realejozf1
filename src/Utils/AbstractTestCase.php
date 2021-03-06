<?php
/**
 * Test case para as funcionalidades padrões
 *
 * Apesar da calsse se chamar abstract ela não é exatamente um.
 * É de propósito.
 *
 * Ela deveria estar dentro de \Test, mas esse namespace quebra o autocomplete
 * do ZendStudio. Como essa biblioteca vai morrer em brave deixei dentro do \Utils mesmo
 *
 * @link      http://bitbucket.org/bffc/excelencia
 * @copyright Copyright (c) 2014 Realejo (http://realejo.com.br)
 * @license   proprietary
 */
namespace RealejoZf1\Utils;

use PHPUnit\Framework\TestCase;

class AbstractTestCase extends TestCase
{
    const SQL_CREATE = 'create';
    const SQL_DROP = 'drop';

    /**
     * @var \Zend_Db_Adapter_Abstract
     */
    protected $adapter = null;

    /**
     * Lista de tabelas que serão criadas e dropadas
     *
     * @var array
     */
    protected $tables = array();

    /**
     * Prepares the environment before running ALL tests.
     */
    public static function setUpBeforeClass()
    {
        // Apaga todo o conteúdo do APPLICATION DATA
        $oTemp = new self();
        $oTemp->clearApplicationData();
    }

    /**
     * Reset the environment after running ALL tests.
     */
    public static function tearDownAfterClass()
    {
        // Apaga todo o conteúdo do APPLICATION DATA
        $oTemp = new self();
        $oTemp->clearApplicationData();
    }

    /**
     * @return \Zend_Db_Adapter_Abstract
     */
    public function getAdapter()
    {
        if (!isset($this->adapter)) {
            $this->adapter = \Zend_Db_Table_Abstract::getDefaultAdapter();
        }
        return $this->adapter;
    }

    /**
     * @return AbstractTestCase
     */
    public function setAdapter(\Zend_Db_Adapter_Abstract $adapter)
    {
        \Zend_Db_Table::setDefaultAdapter($adapter);
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * @param null $tables
     * @return AbstractTestCase
     */
    public function createTables($tables = null)
    {
        // Não deixa executar em produção
        if (APPLICATION_ENV !== 'testing') {
            $this->fail('Só é possível executar createTables() em testing');
        }

        if (empty($tables)) {
            $tables = $this->tables;
        }

        if (empty($tables)) {
            return $this;
        }

        // Recupera o script para criar as tabelas
        foreach($tables as $tbl) {
            // Cria a tabela de usuários
            $this->getAdapter()->query(file_get_contents($this->getSqlFile($tbl, self::SQL_CREATE)));
        }

        return $this;
    }

    /**
     * @param string $file
     * @param string $sqlAction
     * @return string|false
     */
    private function getSqlFile($file, $sqlAction)
    {
        if ($sqlAction === self::SQL_CREATE) {

            // Procura primeiro na pasta do modulo caso queria substituir a do geral
            if (strpos(TEST_ROOT, '/modules') !== false) {
                $modulePath = substr(TEST_ROOT, 0, strpos(TEST_ROOT, '/modules'));
                $paths[] = "$modulePath/tests/assets/sql/$file.sql";
                $paths[] = "$modulePath/tests/assets/sql/$file.create.sql";
            }

            // Procura na pasta geral de teste do aplicativo
            $paths[] = TEST_ROOT . "/assets/sql/$file.sql";
            $paths[] = TEST_ROOT . "/assets/sql/$file.create.sql";

            foreach($paths as $path) {
                if (file_exists($path)) {
                    return $path;
                }
            }

            $this->fail("Arquivo sql não encontrado $file");
        }

        if ($sqlAction === self::SQL_DROP) {
            // Procura primeiro na pasta do modulo caso queria substituir a do geral
            if (strpos(TEST_ROOT, '/modules') !== false) {
                $modulePath = substr(TEST_ROOT, 0, strpos(TEST_ROOT, '/modules'));
                $paths[] = "$modulePath/tests/assets/sql/$file.drop.sql";
            }

            // Procura na pasta geral de teste do aplicativo
            $paths = [
                TEST_ROOT . "/assets/sql/$file.drop.sql"
            ];

            foreach($paths as $path) {
                if (file_exists($path)) {
                    return $path;
                }
            }

            return false;
        }

        $this->fail("Action não definido $sqlAction");

        return null;
    }

    /**
     * @param array $tables
     * @return AbstractTestCase
     */
    public function dropTables(array $tables = null)
    {
        // Não deixa executar em produção
        if (APPLICATION_ENV !== 'testing') {
            $this->fail('Só é possível executar dropTables() em testing');
        }

        if (empty($tables)) {
            $tables = array_reverse($this->tables);
        }

        if (!empty($tables)) {

            // Desabilita os indices e constrains para não dar erro
            // ao apagar uma tabela com foreign key
            // No mundo real isso é inviável, mas nos testes podemos
            // ignorar as foreign keys APÓS os testes
            $this->getAdapter()->query('SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;');
            $this->getAdapter()->query('SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;');
            $this->getAdapter()->query('SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE=\'TRADITIONAL,ALLOW_INVALID_DATES\';');

            // Recupera o script para remover as tabelas
            foreach($tables as $tbl) {
                $tblFile = $this->getSqlFile($tbl, self::SQL_DROP);
                if ($tblFile === false) {
                    $dropCommand = "DROP TABLE IF EXISTS $tbl;";
                } else {
                    $dropCommand = file_get_contents($tblFile);
                }

                $this->getAdapter()->query($dropCommand);
            }

            $this->getAdapter()->query('SET SQL_MODE=@OLD_SQL_MODE;');
            $this->getAdapter()->query('SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;');
            $this->getAdapter()->query('SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;');
        }

        return $this;
    }

    /**
     *
     * @param string  $table
     * @param array $rows
     * @throws \Exception
     *
     * @return AbstractTestCase
     */
    public function insertRows($table, $rows)
    {
        // Não deixa executar em produção
        if (APPLICATION_ENV !== 'testing') {
            $this->fail('Só é possível executar insertRows() em testing');
        }

        if (is_array($table)) {
            $t = $rows;
            $rows = $table;
            $table = $t;
        }

        if (is_string($table)) {
            $table = new \Zend_Db_Table($table);
        } elseif (! $table instanceof \Zend_Db_Table) {
            throw new \Exception("$table deve ser um string ou Zend_Db_Table");
        }

        foreach ($rows as $r) {
            $table->insert($r);
        }

        return $this;
    }

    /**
     * Apaga todas pastas do APPLICATION_DATA
     * @return boolean
     */
    public function clearApplicationData()
    {
        // Não deixa executar em produção
        if (APPLICATION_ENV !== 'testing') {
            $this->fail('Só é possível executar clearApplicationData() em testing');
        }

        // Verifica se há APPLICATION_DATA
        if (!defined('APPLICATION_DATA')) {
            $this->fail('APPLICATION_DATA não definido');
        }

        // Verifica se a pasta existe e tem permissão de escrita
        if (!is_dir(APPLICATION_DATA) || !is_writeable(APPLICATION_DATA)) {
            $this->fail('APPLICATION_DATA não definido');
        }

        // Apaga todo o conteudo dele
        $this->rrmdir(APPLICATION_DATA, APPLICATION_DATA);

        return $this->isApplicationDataEmpty();
    }

    /**
     * Retorna se a pasta APPLICATION_DATA está vazia
     *
     * @return boolean
     */
    public function isApplicationDataEmpty()
    {
        // Verifica se há APPLICATION_DATA
        if (!defined('APPLICATION_DATA')) {
            $this->fail('APPLICATION_DATA não definido');
        }
        // Verifica se a pasta existe e tem permissão de escrita
        if (!is_dir(APPLICATION_DATA) || !is_writeable(APPLICATION_DATA)) {
            $this->fail('APPLICATION_DATA não definido');
        }

        // Retorna se está vazio
        return (count(scandir(APPLICATION_DATA)) == 3);
    }

    /**
     * Apaga recursivamente o contéudo de um pasta
     *
     * @param string $dir
     * @param string $root OPCIONAL pasta raiz para evitar que seja apagada
     */
    public function rrmdir($dir, $root = null)
    {
        // Não deixa executar em produção
        if (APPLICATION_ENV !== 'testing') {
            $this->fail('Só é possível executar rrmdir() em testing');
        }

        // Não deixa apagar fora do APPLICATION DATA
        if (strpos($dir, APPLICATION_DATA) === false || empty(APPLICATION_DATA)) {
            $this->fail('Não é possível apagar fora do APPLICATION_DATA');
        }

        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != ".." && $object != ".gitignore") {
                    if (filetype($dir . "/" . $object) == "dir") {
                        $this->rrmdir($dir . "/" . $object, $root);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }

            // Não apaga a raiz
            if ($dir !== $root && count(scandir($dir)) == 2) {
                rmdir($dir);
            }
        }
    }

    /**
     * Retorna a pasta de assets
     *
     * @param string $path
     *
     * @return string
     */
    protected function getAssetsPath($path = '')
    {
        // Verifica se há APPLICATION_DATA
        if (!defined('APPLICATION_DATA')) {
            $this->fail('APPLICATION_DATA não definido');
        }

        // Verifica se a pasta existe e tem permissão de escrita
        if (!is_dir(APPLICATION_DATA)) {
            $this->fail('APPLICATION_DATA não definido');
        }

        // Path do asset a ser usado
        $path = realpath(APPLICATION_DATA . '/../'. $path);

        // Verifica se a pasta existe e tem permissão de escrita
        if (empty($path) || !is_dir($path)) {
            $this->fail(APPLICATION_DATA . "/../$path não definido");
        }

        return $path;
    }


    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokePrivateMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }


    /**
     * Retorna as tabelas padrões
     *
     * @return array
     */
    public function getTables()
    {
        return $this->tables;
    }

    /**
     * Define as tabelas a serem usadas com padrão
     *
     * @param array $tables
     *
     * @return AbstractTestCase
     */
    public function setTables($tables)
    {
        $this->tables = $tables;

        return $this;
    }
}
