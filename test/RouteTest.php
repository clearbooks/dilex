<?php
declare(strict_types=1);

namespace Clearbooks\Dilex;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    /**
     * @var Route
     */
    private $route;

    public function setUp(): void
    {
        parent::setUp();
        $this->route = new Route('/test', '');
    }

    #[Test]
    public function WhenCallingAssert_ExpectRequirementAdded()
    {
        $this->route->assert('id', 'test');
        $this->assertEquals('test', $this->route->getRequirements()['id']);
    }

    #[Test]
    public function WhenCallingBefore_ExpectBeforeControllerListenersAdded()
    {
        $testCallback = [self::class, 'setUp'];
        $this->route->before($testCallback);
        $this->assertEquals(
                [$testCallback],
                $this->route->getBeforeCallbacks()
        );

        $testCallback2 = [self::class, 'tearDown'];
        $this->route->before($testCallback2);
        $this->assertEquals(
                [$testCallback, $testCallback2],
                $this->route->getBeforeCallbacks()
        );
    }

    #[Test]
    public function WhenCallingAfter_ExpectAfterControllerListenersAdded()
    {
        $testCallback = [self::class, 'setUp'];
        $this->route->after($testCallback);
        $this->assertEquals(
                [$testCallback],
                $this->route->getAfterCallbacks()
        );

        $testCallback2 = [self::class, 'tearDown'];
        $this->route->after($testCallback2);
        $this->assertEquals(
                [$testCallback, $testCallback2],
                $this->route->getAfterCallbacks()
        );
    }
}
