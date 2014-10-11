<?php

use Dero\Core\ResourceManager;

class ResourceManagerTest extends PHPUnit_Framework_TestCase
{
    public function testScript()
    {
        ResourceManager::AddScript('angular');
        $strRet = ResourceManager::LoadScripts();
        $this->assertNotEmpty($strRet);

        // loads a dependency
        $this->assertRegExp(
            '/<script.*jquery-1\.11\.0\.min\.js.*\/script/',
            $strRet,
            'Failed loading script dependency'
        )
        ;
        // loads itself
        $this->assertRegExp(
            '/<script.*angular\.min\.js.*\/script/',
            $strRet,
            'Failed loading requested script'
        );
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testScriptFail()
    {
        ResourceManager::AddScript('non-existent');
    }

    public function testStyle()
    {
        ResourceManager::AddStyle('site');
        $strRet = ResourceManager::LoadStyles();
        $this->assertNotEmpty($strRet);

        // loads its style tag
        $this->assertRegExp(
            '/<link.*site\.css.*\/>/',
            $strRet,
            'Failed loading css tag'
        );
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testStyleFail()
    {
        ResourceManager::AddStyle('non-existent');
    }
}