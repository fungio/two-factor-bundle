<?php

namespace Fungio\TwoFactorBundle\Tests\Event;

use Fungio\Api\Code\AcceptedCode;
use Fungio\TwoFactorBundle\Event\CodeCheckEvent;

class CodeCheckEventTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $code  = new AcceptedCode([]);
        $event = new CodeCheckEvent($code);
        $this->assertEquals($code, $event->getCode());
    }
}
