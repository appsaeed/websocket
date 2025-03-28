<?php

namespace Appsaeed;

use PHPUnit\Framework\TestCase;
use Appsaeed\Mock\ConnectionDecorator;
use PHPUnit\Framework\Error\Warning;
use PHPUnit\Framework\TestStatus\Warning as TestStatusWarning;

/**
 * @covers Appsaeed\AbstractConnectionDecorator
 * @covers Appsaeed\ConnectionInterface
 */
class AbstractConnectionDecoratorTest extends TestCase
{
    protected $mock;
    protected $l1;
    protected $l2;

    protected function setUp(): void
    {
        $this->mock = $this->createMock(\Appsaeed\ConnectionInterface::class);
        $this->l1   = new ConnectionDecorator($this->mock);
        $this->l2   = new ConnectionDecorator($this->l1);
    }

    public function testGet()
    {
        $var = 'hello';
        $val = 'world';

        $this->mock->$var = $val;

        $this->assertEquals($val, $this->l1->$var);
        $this->assertEquals($val, $this->l2->$var);
    }

    public function testSet()
    {
        $var = 'Chris';
        $val = 'Boden';

        $this->l1->$var = $val;

        $this->assertEquals($val, $this->mock->$var);
    }

    public function testSetLevel2()
    {
        $var = 'Try';
        $val = 'Again';

        $this->l2->$var = $val;

        $this->assertEquals($val, $this->mock->$var);
    }

    public function testIsSetTrue()
    {
        $var = 'PHP';
        $val = 'Appsaeed';

        $this->mock->$var = $val;

        $this->assertTrue(isset($this->l1->$var));
        $this->assertTrue(isset($this->l2->$var));
    }

    public function testIsSetFalse()
    {
        $var = 'herp';

        $this->assertFalse(isset($this->l1->$var));
        $this->assertFalse(isset($this->l2->$var));
    }

    public function testUnset()
    {
        $var = 'Flying';
        $val = 'Monkey';

        $this->mock->$var = $val;
        unset($this->l1->$var);

        $this->assertFalse(isset($this->mock->$var));
    }

    public function testUnsetLevel2()
    {
        $var = 'Flying';
        $val = 'Monkey';

        $this->mock->$var = $val;
        unset($this->l2->$var);

        $this->assertFalse(isset($this->mock->$var));
    }

    public function testGetConnection()
    {
        $class  = new \ReflectionClass(AbstractConnectionDecorator::class);
        $method = $class->getMethod('getConnection');
        $method->setAccessible(true);

        $conn = $method->invokeArgs($this->l1, []);

        $this->assertSame($this->mock, $conn);
    }

    public function testGetConnectionLevel2()
    {
        $class  = new \ReflectionClass(AbstractConnectionDecorator::class);
        $method = $class->getMethod('getConnection');
        $method->setAccessible(true);

        $conn = $method->invokeArgs($this->l2, []);

        $this->assertSame($this->l1, $conn);
    }

    public function testWrapperCanStoreSelfInDecorator()
    {
        $this->mock->decorator = $this->l1;

        $this->assertSame($this->l1, $this->l2->decorator);
    }

    public function testDecoratorRecursion()
    {
        $this->mock->decorator = new \stdClass;
        $this->mock->decorator->conn = $this->l1;

        $this->assertSame($this->l1, $this->mock->decorator->conn);
        $this->assertSame($this->l1, $this->l1->decorator->conn);
        $this->assertSame($this->l1, $this->l2->decorator->conn);
    }

    public function testDecoratorRecursionLevel2()
    {
        $this->mock->decorator = new \stdClass;
        $this->mock->decorator->conn = $this->l2;

        $this->assertSame($this->l2, $this->mock->decorator->conn);
        $this->assertSame($this->l2, $this->l1->decorator->conn);
        $this->assertSame($this->l2, $this->l2->decorator->conn);

        // Just for fun
        $this->assertSame($this->l2, $this->l2->decorator->conn->decorator->conn->decorator->conn);
    }

    public function testWarningGettingNothing()
    {
        $this->expectException(TestStatusWarning::class);
        $var = $this->mock->nonExistant;
    }

    public function testWarningGettingNothingLevel1()
    {
        $this->expectException(TestStatusWarning::class);
        $var = $this->l1->nonExistant;
    }

    public function testWarningGettingNothingLevel2()
    {
        $this->expectException(TestStatusWarning::class);
        $var = $this->l2->nonExistant;
    }
}
