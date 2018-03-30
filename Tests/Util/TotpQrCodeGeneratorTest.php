<?php

namespace TwoFAS\TwoFactorBundle\Tests\Util;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use TwoFAS\Api\QrCode\EndroidQrClient;
use TwoFAS\Api\QrCodeGenerator;
use TwoFAS\TwoFactorBundle\Util\TotpQrCodeGenerator;

class TotpQrCodeGeneratorTest extends KernelTestCase
{
    /**
     * @var TotpQrCodeGenerator
     */
    protected $generator;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->generator = new TotpQrCodeGenerator(new QrCodeGenerator(new EndroidQrClient()));
    }


    public function testGenerate()
    {
        $prefix = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAUwAAAFMAQMAAACaoBccAAAABlBMVEX';
        $result = $this->generator->generate('JBSWY3DPEHPK3PXP', '42c533dc1d6d01323807777f896cb305', 'test');

        $this->assertStringStartsWith($prefix, $result);
    }
}
