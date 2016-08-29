<?php
namespace RealejoZf1Test\Metadata;

use RealejoZf1\Metadata\MetadataService;
use RealejoZf1\Stdlib\ArrayObject;
use RealejoZf1\Test\BaseTestCase;

/**
 * MetadataService test case.
 */
class MetadataServiceTest extends BaseTestCase
{

    /**
     *
     * @var MetadataService
     */
    private $metadataService;

    private $schema = array(
        array(
            'cd_info' => 123,
            'tipo' => MetadataService::BOOLEAN,
            'nick' => 'bool'
        ),
        array(
            'cd_info' => 321,
            'tipo' => MetadataService::DATE,
            'nick' => 'date'
        ),
        array(
            'cd_info' => 159,
            'tipo' => MetadataService::DATETIME,
            'nick' => 'datetime'
        ),
        array(
            'cd_info' => 753,
            'tipo' => MetadataService::DECIMAL,
            'nick' => 'decimal'
        ),
        array(
            'cd_info' => 78,
            'tipo' => MetadataService::INTEGER,
            'nick' => 'integer'
        )
        ,
        array(
            'cd_info' => 456,
            'tipo' => MetadataService::TEXT,
            'nick' => 'text'
        )
    );

    private $cacheFetchAllKey;

    private $cacheSchemaKey = 'metadataschema_metadata_schema';

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

        // TODO Auto-generated MetadataServiceTest::setUp()

        $this->metadataService = new MetadataService();

        $this->metadataService
             ->setMapper('RealejoZf1Test\Metadata\MetadataMapperReference')
             ->setMetadataMappers('metadata_schema', 'metadata_value', 'fk_reference')
             ->setUseCache(true);

         $this->cacheFetchAllKey = 'fetchAll'.md5(var_export(false, true) . var_export(null, true) . var_export(null, true) . var_export(null, true) . var_export(null, true));

         // Grava no cache um fetchAll ficticio
         $fetchAll = array();
         foreach($this->schema as $row)
         {
             $fetchAll[] = new ArrayObject($row);
         }
         $this->metadataService
              ->getCache()
              ->save($fetchAll, $this->cacheFetchAllKey);

         $this->assertEquals($fetchAll, $this->metadataService->getCache()->load($this->cacheFetchAllKey));

         // Cria o schema associado pelo id
         $schemaById = array();
         foreach ($this->schema as $s) {
             $schemaById[$s['cd_info']] = $s;
         }

        // Grava no cache um metada ficticio
        $this->metadataService
             ->getCache()
             ->save($schemaById, $this->cacheSchemaKey);

         $this->assertEquals($schemaById, $this->metadataService->getCache()->load($this->cacheSchemaKey));
    }

    private function createTableSchema()
    {
        $this->createTables(array('metadata_schema', 'metadata_value'));
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->metadataService->cleanCache();
        $this->metadataService = null;
        $this->dropTables(array('metadata_schema', 'metadata_value'));
        parent::tearDown();
    }

    /**
     * Tests MetadataService->getSchemaByKeyNames()
     */
    public function testGetSchemaByKeyNames()
    {
        // Cria o schema exemplo para keyname
        $schemaByKeyname = array();
        foreach ($this->schema as $s) {
            $schemaByKeyname[$s['nick']] = $s;
        }
        $this->assertEquals($schemaByKeyname, $this->metadataService->getSchemaByKeyNames());
        $this->assertEquals($schemaByKeyname, $this->metadataService->getSchemaByKeyNames(true));
        $this->assertEquals($schemaByKeyname, $this->metadataService->getSchemaByKeyNames(false));
    }

    /**
     * Tests MetadataService->getCorrectSetKey()
     */
    public function testGetCorrectSetKey()
    {
        $service = new MetadataService();
        $reflection = new \ReflectionClass(get_class($service));
        $method = $reflection->getMethod('getCorrectSetKey');
        $method->setAccessible(true);

        $this->assertEquals('value_boolean', $method->invokeArgs($service, array(array('tipo'=>MetadataService::BOOLEAN))));
        $this->assertEquals('value_date', $method->invokeArgs($service, array(array('tipo'=>MetadataService::DATE))));
        $this->assertEquals('value_datetime', $method->invokeArgs($service, array(array('tipo'=>MetadataService::DATETIME))));
        $this->assertEquals('value_decimal', $method->invokeArgs($service, array(array('tipo'=>MetadataService::DECIMAL))));
        $this->assertEquals('value_integer', $method->invokeArgs($service, array(array('tipo'=>MetadataService::INTEGER))));
        $this->assertEquals('value_text', $method->invokeArgs($service, array(array('tipo'=>MetadataService::TEXT))));

    }

    /**
     * Tests MetadataService->getCorrectSetKey()
     */
    public function testGetCorrectSetValue()
    {
        $service = new MetadataService();
        $reflection = new \ReflectionClass(get_class($service));
        $method = $reflection->getMethod('getCorrectSetValue');
        $method->setAccessible(true);

        $this->assertEquals(1, $method->invokeArgs($service, array(array('tipo'=>MetadataService::BOOLEAN), 1)));
        $this->assertEquals(1, $method->invokeArgs($service, array(array('tipo'=>MetadataService::BOOLEAN), true)));
        $this->assertEquals(0, $method->invokeArgs($service, array(array('tipo'=>MetadataService::BOOLEAN), 0)));
        $this->assertEquals(0, $method->invokeArgs($service, array(array('tipo'=>MetadataService::BOOLEAN), false)));

        $this->assertNull($method->invokeArgs($service, array(array('tipo'=>MetadataService::BOOLEAN), null)));
        $this->assertEquals(0, $method->invokeArgs($service, array(array('tipo'=>MetadataService::BOOLEAN), '')));

        $this->assertEquals('2016-12-10', $method->invokeArgs($service, array(array('tipo'=>MetadataService::DATE), '10/12/2016')));
        $this->assertEquals('2016-12-10', $method->invokeArgs($service, array(array('tipo'=>MetadataService::DATE), '10/12/2016 14:25:24')));
        $this->assertEquals('0', $method->invokeArgs($service, array(array('tipo'=>MetadataService::DATE), '0')));
        $this->assertNull(null, $method->invokeArgs($service, array(array('tipo'=>MetadataService::DATE), null)));

        $this->assertEquals('value_datetime', $method->invokeArgs($service, array(array('tipo'=>MetadataService::DATETIME), 'value_datetime')));
        $this->assertEquals('2016-12-10 00:00:00', $method->invokeArgs($service, array(array('tipo'=>MetadataService::DATETIME), '10/12/2016')));
        $this->assertEquals('2016-12-10 13:13:12', $method->invokeArgs($service, array(array('tipo'=>MetadataService::DATETIME), '10/12/2016 13:13:12')));

        $this->assertEquals(0, $method->invokeArgs($service, array(array('tipo'=>MetadataService::DECIMAL), 'value_decimal')));
        $this->assertEquals('0', $method->invokeArgs($service, array(array('tipo'=>MetadataService::DECIMAL), 'value_decimal')));
        $this->assertEquals(0, $method->invokeArgs($service, array(array('tipo'=>MetadataService::INTEGER), 'value_integer')));
        $this->assertEquals('0', $method->invokeArgs($service, array(array('tipo'=>MetadataService::INTEGER), 'value_integer')));
        $this->assertEquals('value_text', $method->invokeArgs($service, array(array('tipo'=>MetadataService::TEXT), 'value_text')));

        $this->assertNull($method->invokeArgs($service, array(array('tipo'=>MetadataService::DECIMAL), null)));
        $this->assertNull($method->invokeArgs($service, array(array('tipo'=>MetadataService::INTEGER), null)));
        $this->assertNull($method->invokeArgs($service, array(array('tipo'=>MetadataService::TEXT), null)));
    }

    /**
     * Tests MetadataService->getMapperSchema()
     */
    public function testGetMappersSchema()
    {
        $service = new MetadataService();
        $this->assertNull($service->getMapperSchema());
        $this->assertNull($service->getMapperValue());
        $this->assertInstanceOf('\RealejoZf1\Metadata\MetadataService', $service->setMetadataMappers('schemaTable', 'valuesTable', 'foreignKeyName'));
        $this->assertInstanceOf('\RealejoZf1\Metadata\MetadataMapper', $service->getMapperSchema());
        $this->assertEquals('schemaTable', $service->getMapperSchema()->getTableName());
        $this->assertInstanceOf('\RealejoZf1\Metadata\MetadataMapper', $service->getMapperValue());
        $this->assertEquals('valuesTable', $service->getMapperValue()->getTableName());
        $this->assertEquals(array('cd_info', 'foreignKeyName'), $service->getMapperValue()->getTableKey());
        $this->assertEquals('cd_info', $service->getMapperValue()->getTableKey(true));
    }

    public function testCache()
    {
        $service = new MetadataService();
        $this->assertInstanceOf('\RealejoZf1\Metadata\MetadataService', $service->setMetadataMappers('tableone', 'tablesecond', 'keyname'));

        $this->assertFalse($service->getUseCache());
        $this->assertFalse($service->getMapperSchema()->getUseCache());
        $this->assertFalse($service->getMapperValue()->getUseCache());

        $this->assertInstanceOf('\RealejoZf1\Metadata\MetadataService', $service->setUseCache(true));
        $this->assertTrue($service->getUseCache());
        $this->assertTrue($service->getMapperSchema()->getUseCache());
        $this->assertTrue($service->getMapperValue()->getUseCache());

        $this->assertInstanceOf('\RealejoZf1\Metadata\MetadataService', $service->setUseCache(false));

        $this->assertFalse($service->getUseCache());
        $this->assertFalse($service->getMapperSchema()->getUseCache());
        $this->assertFalse($service->getMapperValue()->getUseCache());

        $this->assertInstanceOf('\RealejoZf1\Metadata\MetadataService', $service->setUseCache(true));

        $this->assertTrue($service->getCache()->save('servicedata', 'servicekey'));
        $this->assertNotEmpty($service->getCache()->test('servicekey'));
        $this->assertEquals('servicedata', $service->getCache()->load('servicekey'));

        $this->assertTrue($service->getMapperSchema()->getCache()->save('schemadata', 'schemakey'));
        $this->assertNotEmpty($service->getMapperSchema()->getCache()->test('schemakey'));
        $this->assertNotEmpty($service->getCache()->test('schemakey'));
        $this->assertEquals('schemadata', $service->getMapperSchema()->getCache()->load('schemakey'));
        $this->assertEquals('schemadata', $service->getCache()->load('schemakey'));

        $this->assertTrue($service->getMapperValue()->getCache()->save('valuedata', 'valuekey'));
        $this->assertNotEmpty($service->getMapperValue()->getCache()->test('valuekey'));
        $this->assertNotEmpty($service->getCache()->test('valuekey'));
        $this->assertEquals('valuedata', $service->getMapperValue()->getCache()->load('valuekey'));
        $this->assertEquals('valuedata', $service->getCache()->load('valuekey'));

        $this->assertTrue($service->getCache()->clean());

        $this->assertFalse($service->getCache()->test('servicekey'));
        $this->assertFalse($service->getCache()->load('servicekey'));
        $this->assertFalse($service->getCache()->test('schemakey'));
        $this->assertFalse($service->getCache()->load('schemakey'));
        $this->assertFalse($service->getCache()->test('valuekey'));
        $this->assertFalse($service->getCache()->load('valuekey'));
        $this->assertFalse($service->getMapperSchema()->getCache()->test('schemakey'));
        $this->assertFalse($service->getMapperSchema()->getCache()->load('schemakey'));
        $this->assertFalse($service->getMapperValue()->getCache()->test('valuekey'));
        $this->assertFalse($service->getMapperValue()->getCache()->load('valuekey'));

    }

    /**
     * Tests MetadataService->getSchema()
     */
    public function testGetSchema()
    {
        // Cria o schema associado pelo id
        $schemaById = array();
        foreach ($this->schema as $s) {
            $schemaById[$s['cd_info']] = $s;
        }

        $this->assertEquals($schemaById, $this->metadataService->getSchema());
        $this->assertEquals($schemaById, $this->metadataService->getSchema(true));
        $this->assertEquals($schemaById, $this->metadataService->getSchema(false));

        // apaga o cache do schema, mas mantem do fetchAll
        $this->assertTrue($this->metadataService->getCache()->remove($this->cacheSchemaKey));

        $this->assertEquals($schemaById, $this->metadataService->getSchema());
        $this->assertEquals($schemaById, $this->metadataService->getSchema(true));
        $this->assertEquals($schemaById, $this->metadataService->getSchema(false));
    }

    /**
     * Tests MetadataMapper->getWhere()
     * @depends testGetMappersSchema
     */
    public function testGetWhere()
    {
        $this->assertInternalType('array', $this->metadataService->getWhere(array()));
        $this->assertEquals(array(), $this->metadataService->getWhere(array()));

        $this->assertNull($this->metadataService->getWhere(null));

        $this->assertInternalType('array', $this->metadataService->getWhere(array('metadata'=>array())));
        $this->assertEquals(array(), $this->metadataService->getWhere(array('metadata'=>array())));

        $this->assertInternalType('array', $this->metadataService->getWhere(array('metadata'=>null)));
        $this->assertEquals(array(), $this->metadataService->getWhere(array('metadata'=>null)));
    }

    /**
     * Tests MetadataMapper->getWhere()
     * @depends testGetMappersSchema
     */
    public function testGetWhereBoolean()
    {
        // Cria as tabelas
        $this->createTableSchema();

        $where = $this->metadataService->getWhere(array('metadata'=>array('bool' => true)));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(123)} AND value_boolean = 1)", $where[0]);

        $where = $this->metadataService->getWhere(array('bool' => true));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(123)} AND value_boolean = 1)", $where[0]);

        $where = $this->metadataService->getWhere(array('metadata'=>array('bool' => false)));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(123)} AND value_boolean = 0)", $where[0]);

        $where = $this->metadataService->getWhere(array('bool' => false));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(123)} AND value_boolean = 0)", $where[0]);

        $where = $this->metadataService->getWhere(array('metadata'=>array('bool' => 1)));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(123)} AND value_boolean = 1)", $where[0]);

        $where = $this->metadataService->getWhere(array('bool' => 1));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(123)} AND value_boolean = 1)", $where[0]);

        $where = $this->metadataService->getWhere(array('metadata'=>array('bool' => 0)));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(123)} AND value_boolean = 0)", $where[0]);

        $where = $this->metadataService->getWhere(array('bool' => 0));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(123)} AND value_boolean = 0)", $where[0]);

        $where = $this->metadataService->getWhere(array('metadata'=>array('bool' => null)));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(123)} AND value_boolean IS NULL) OR NOT EXISTS ({$this->getSqlSchemaString(123)})", $where[0]);

        $where = $this->metadataService->getWhere(array('bool' => null));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(123)} AND value_boolean IS NULL) OR NOT EXISTS ({$this->getSqlSchemaString(123)})", $where[0]);

        /*  array(
         'cd_info' => 321,
         'tipo' => MetadataService::DATE,
         'nick' => 'date'
         ),
         array(
         'cd_info' => 159,
         'tipo' => MetadataService::DATETIME,
         'nick' => 'datetime'
         ),
         array(
         'cd_info' => 753,
         'tipo' => MetadataService::DECIMAL,
         'nick' => 'decimal'
         ),
         ) */

    }

    /**
     * Tests MetadataMapper->getWhere()
     * @depends testGetMappersSchema
     */
    public function testGetWhereInteger()
    {
        // Cria as tabelas
        $this->createTableSchema();

        $where = $this->metadataService->getWhere(array('metadata'=>array('integer' => 10)));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(78)} AND value_integer = 10)", $where[0]);

        $where = $this->metadataService->getWhere(array('integer' => 10));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(78)} AND value_integer = 10)", $where[0]);

        $where = $this->metadataService->getWhere(array('metadata'=>array('integer' => 0)));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(78)} AND value_integer = 0)", $where[0]);

        $where = $this->metadataService->getWhere(array('integer' => 0));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(78)} AND value_integer = 0)", $where[0]);

        $where = $this->metadataService->getWhere(array('metadata'=>array('integer' => null)));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(78)} AND value_integer IS NULL) OR NOT EXISTS ({$this->getSqlSchemaString(78)})", $where[0]);

        $where = $this->metadataService->getWhere(array('integer' => null));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(78)} AND value_integer IS NULL) OR NOT EXISTS ({$this->getSqlSchemaString(78)})", $where[0]);

        $where = $this->metadataService->getWhere(array('integer' => -99));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(78)} AND value_integer = -99)", $where[0]);
    }

    /**
     * Tests MetadataMapper->getWhere()
     * @depends testGetMappersSchema
     */
    public function testGetWhereString()
    {
        // Cria as tabelas
        $this->createTableSchema();

        $where = $this->metadataService->getWhere(array('metadata'=>array('text' => 10)));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(456)} AND value_text = 10)", $where[0]);

        $where = $this->metadataService->getWhere(array('text' => 10));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(456)} AND value_text = 10)", $where[0]);

        $where = $this->metadataService->getWhere(array('metadata'=>array('text' => 0)));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(456)} AND value_text = 0)", $where[0]);

        $where = $this->metadataService->getWhere(array('text' => 0));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(456)} AND value_text = 0)", $where[0]);

        $where = $this->metadataService->getWhere(array('metadata'=>array('text' => 'qwerty')));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(456)} AND value_text = 'qwerty')", $where[0]);

        $where = $this->metadataService->getWhere(array('text' => 'qwerty'));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(456)} AND value_text = 'qwerty')", $where[0]);

        $where = $this->metadataService->getWhere(array('metadata'=>array('text' => '')));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(456)} AND value_text IS NULL) OR NOT EXISTS ({$this->getSqlSchemaString(456)})", $where[0]);

        $where = $this->metadataService->getWhere(array('text' => ''));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(456)} AND value_text IS NULL) OR NOT EXISTS ({$this->getSqlSchemaString(456)})", $where[0]);


        $where = $this->metadataService->getWhere(array('metadata'=>array('text' => null)));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(456)} AND value_text IS NULL) OR NOT EXISTS ({$this->getSqlSchemaString(456)})", $where[0]);

        $where = $this->metadataService->getWhere(array('text' => null));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(456)} AND value_text IS NULL) OR NOT EXISTS ({$this->getSqlSchemaString(456)})", $where[0]);
    }

    /**
     * Tests MetadataMapper->getWhere()
     * @depends testGetMappersSchema
     */
    public function testGetWhereDate()
    {
        // Cria as tabelas
        $this->createTableSchema();

        $where = $this->metadataService->getWhere(array('metadata'=>array('date' => '15/10/2016')));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(321)} AND value_date = '2016-10-15')", $where[0]);

        $where = $this->metadataService->getWhere(array('date' => '15/10/2016'));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(321)} AND value_date = '2016-10-15')", $where[0]);


        $where = $this->metadataService->getWhere(array('metadata'=>array('date' => '2016-10-15')));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(321)} AND value_date = '2016-10-15')", $where[0]);

        $where = $this->metadataService->getWhere(array('date' => '2016-10-15'));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(321)} AND value_date = '2016-10-15')", $where[0]);


        $where = $this->metadataService->getWhere(array('metadata'=>array('date' => '15/10/2016 14:24:35')));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(321)} AND value_date = '2016-10-15')", $where[0]);

        $where = $this->metadataService->getWhere(array('date' => '15/10/2016 14:24:35'));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(321)} AND value_date = '2016-10-15')", $where[0]);


        $where = $this->metadataService->getWhere(array('metadata'=>array('date' => '2016-10-15 14:24:35')));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(321)} AND value_date = '2016-10-15')", $where[0]);

        $where = $this->metadataService->getWhere(array('date' => '2016-10-15 14:24:35'));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(321)} AND value_date = '2016-10-15')", $where[0]);


        $where = $this->metadataService->getWhere(array('metadata'=>array('date' => '')));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(321)} AND value_date = '')", $where[0]);

        $where = $this->metadataService->getWhere(array('date' => ''));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(321)} AND value_date = '')", $where[0]);

        $where = $this->metadataService->getWhere(array('metadata'=>array('date' => null)));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(321)} AND value_date IS NULL) OR NOT EXISTS ({$this->getSqlSchemaString(321)})", $where[0]);

        $where = $this->metadataService->getWhere(array('date' => null));
        $this->assertInternalType('array', $where);
        $this->assertCount(1, $where);
        $this->assertInstanceOf('\Zend_Db_Expr', $where[0]);
        $this->assertEquals("EXISTS ({$this->getSqlSchemaString(321)} AND value_date IS NULL) OR NOT EXISTS ({$this->getSqlSchemaString(321)})", $where[0]);
    }

    private function getSqlSchemaString($idInfo)
    {
        return "SELECT * FROM metadata_value WHERE cd_info=$idInfo AND tblreference.id_reference=metadata_value.fk_reference";
    }

    /**
     * Tests MetadataService->getValues()
     */
    public function testGetValues()
    {
        // TODO Auto-generated MetadataServiceTest->testGetValues()
        $this->markTestIncomplete("getValues test not implemented");

        $this->metadataService->getValues(/* parameters */);
    }

    /**
     * Tests MetadataService->saveMetadata()
     */
    public function testSaveMetadata()
    {
        // TODO Auto-generated MetadataServiceTest->testSaveMetadata()
        $this->markTestIncomplete("saveMetadata test not implemented");

        $this->metadataService->saveMetadata(/* parameters */);
    }

    /**
     * Tests MetadataService->getLastSaveMetadataLog()
     */
    public function testGetLastSaveMetadataLog()
    {
        // TODO Auto-generated MetadataServiceTest->testGetLastSaveMetadataLog()
        $this->markTestIncomplete("getLastSaveMetadataLog test not implemented");

        $this->metadataService->getLastSaveMetadataLog(/* parameters */);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage schemaTable invalid
     */
    function testSetSchemaMapper()
    {
        $service = new MetadataService();
        $service->setMetadataMappers(new \RealejoZf1\Metadata\MetadataMapper('tablename', 'keyname'), null, null);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage valueTable invalid
     */
    function testSetValuesMapper()
    {
        $service = new MetadataService();
        $service->setMetadataMappers('tableone', new \RealejoZf1\Metadata\MetadataMapper('tablename', 'keyname'), null);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage mapperForeignKey invalid
     */
    function testSetForeignKey()
    {
        $service = new MetadataService();
        $service->setMetadataMappers('tableone', 'tableone', null);
    }
}

