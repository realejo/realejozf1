<?php

namespace RealejoZf1Test\Utils;

use RealejoZf1\Utils\Date;

/**
 * Date test case.
 */
class DateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests Date::isFormat()
     */
    public function testIsFormat()
    {

        $data = '10/09/2012';
        $this->assertTrue(Date::isFormat('d/m/Y', $data));
        $this->assertFalse(Date::isFormat('dmY', $data));
        $this->assertFalse(Date::isFormat('d/m/Y H:i:s', $data));

        $data = '10092012';
        $this->assertFalse(Date::isFormat('d/m/Y', $data));
        $this->assertTrue(Date::isFormat('dmY', $data));

        $data = '10092012 00:00:00';
        $this->assertFalse(Date::isFormat('d/m/Y H:i:s', $data ));
        $this->assertTrue(Date::isFormat('dmY H:i:s', $data ));
        $this->assertFalse(Date::isFormat('d/m/Y h:i:s', $data ));
        $this->assertTrue(Date::isFormat('dmY h:i:s', $data ));
        $this->assertFalse(Date::isFormat('d/m/Y', $data ));
        $this->assertFalse(Date::isFormat('dmY', $data ));

        $data = '10092012 000000';
        $this->assertFalse(Date::isFormat('d/m/Y His', $data ));
        $this->assertTrue(Date::isFormat('dmY His', $data ));
        $this->assertFalse(Date::isFormat('d/m/Y', $data ));
        $this->assertFalse(Date::isFormat('dmY', $data ));
    }
}
