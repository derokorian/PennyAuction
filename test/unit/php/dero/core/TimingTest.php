<?php

use Dero\Core\Timing;

class TimingTest extends PHPUnit_Framework_TestCase
{
    use \Test\Unit\Php\Traits\assertHeaders;

    public function testTiming()
    {
        Timing::start('test');
        usleep(100);
        Timing::end('test');

        $this->expectOutputRegex('/test timing: [0-9.]+ms/');
        Timing::printTimings();

        $aTimes = Timing::getTimings();
        $this->assertNotEmpty($aTimes);
        $this->assertArrayHasKey('test', $aTimes);

        Timing::setHeaderTimings();
        $this->assertHeaderSet('X-Test-Timing', 'ms');
    }
}