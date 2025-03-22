<?php

namespace Appsaeed\Session\Serialize;

use PHPUnit\Framework\TestCase;
use Appsaeed\Session\Serialize\PhpHandler;

/**
 * @covers Appsaeed\Session\Serialize\PhpHandler
 */
class PhpHandlerTest extends TestCase
{
    protected $_handler;

    protected function setUp(): void
    {
        $this->_handler = new PhpHandler;
    }

    public function serializedProvider(): array
    {
        return [
            [
                '_sf2_attributes|a:2:{s:5:"hello";s:5:"world";s:4:"last";i:1332872102;}_sf2_flashes|a:0:{}',
                [
                    '_sf2_attributes' => [
                        'hello' => 'world',
                        'last'  => 1332872102
                    ],
                    '_sf2_flashes' => []
                ]
            ]
        ];
    }

    /**
     * @dataProvider serializedProvider
     */
    public function testUnserialize(string $in, array $expected): void
    {
        $this->assertEquals($expected, $this->_handler->unserialize($in));
    }

    /**
     * @dataProvider serializedProvider
     */
    public function testSerialize(string $serialized, array $original): void
    {
        $this->assertEquals($serialized, $this->_handler->serialize($original));
    }
}
