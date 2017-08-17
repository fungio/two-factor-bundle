<?php

namespace TwoFAS\TwoFactorBundle\Tests\Event;

use TwoFAS\Api\Code\AcceptedCode;
use TwoFAS\TwoFactorBundle\Event\CodeCheckEvent;

class CodeCheckEventTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $code  = new AcceptedCode([]);
        $event = new CodeCheckEvent($code);
        $this->assertEquals($code, $event->getCode());
    }
}
