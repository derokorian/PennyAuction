<?php

use Dero\Core\Config;

class ConfigTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        file_put_contents(
            ROOT . DS . 'dero/config/test.json',
            json_encode([
                'user' => 'myUser',
                'pass' => 'myPass',
                'complex' => [
                    'configuration' => [
                        'structure' => true
                    ]
                ]
            ])
        );

        file_put_contents(
            ROOT . DS . 'config/test.json',
            json_encode([
                'user' => 'myUser',
                'pass' => 'testPass',
                'custom' => 1234
            ])
        );
    }

    public function tearDown()
    {
        unlink(ROOT . DS . 'dero/config/test.json');
        unlink(ROOT . DS . 'config/test.json');
    }

    public function testSimple()
    {
        $this->assertEquals(
            'myUser',
            Config::GetValue('test','user')
        );

        $this->assertEquals(
            'testPass',
            Config::GetValue('test','pass')
        );

        $this->assertEquals(
            1234,
            Config::GetValue('test','custom')
        );
    }

    public function testComplex()
    {
        $this->assertEquals(
            true,
            Config::GetValue(
                'test',
                'complex',
                'configuration',
                'structure'
            )
        );
    }

    public function testUnknownIsNull()
    {
        $this->assertEquals(
            null,
            Config::GetValue('test', 'fake')
        );
    }

    public function testEmptyIsNull()
    {
        $this->assertEquals(
            null,
            Config::GetValue()
        );
    }
}