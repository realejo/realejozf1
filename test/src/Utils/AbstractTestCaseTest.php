<?php
namespace RealejoZf1Test;

use RealejoZf1\Utils\AbstractTestCase as AbstractClass;

/**
 * Token test case.
 */
class AbstractTestCaseTest extends AbstractClass
{

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();

       $this->createTables(array('album'));
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {

        $this->dropTables(array('album'));

        parent::tearDown();
    }

    public function testBase()
    {
        $conn = $this->getAdapter();
        $this->assertNotNull($conn);
        $this->assertInstanceOf('Zend_Db_Adapter_Abstract', $conn);
    }
}

