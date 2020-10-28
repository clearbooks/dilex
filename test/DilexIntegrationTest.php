<?php
declare(strict_types=1);

namespace Clearbooks\Dilex;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class DilexIntegrationTest extends TestCase
{
    private const TEST_PAYLOAD = [
        'test1' => 'value1',
        'test2' => 'value2'
    ];
    private const ECHO_ENDPOINT = '/echo';
    private const ERROR_ENDPOINT = '/error';

    /**
     * @var MockContainer
     */
    private $mockContainer;

    /**
     * @var Dilex
     */
    private $app;

    /**
     * @var null|Throwable
     */
    private $error;

    /**
     * @var int
     */
    private static $envCnt = 0;

    public function setUp(): void
    {
        parent::setUp();
        $this->mockContainer = new MockContainer([]);
        // Required, so we can set up our application for each test case separately
        $environment = str_replace('\\', '_', self::class ) . self::$envCnt++;
        $this->app = new Dilex($environment, false, $this->mockContainer);
        $this->app->error(function (Throwable $e) {
            $this->error = $e;
        });
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->app->shutdown();
        $fileSystem = new Filesystem();
        $fileSystem->remove( $this->app->getCacheDir() );
    }

    private function runAndGetResponse(Request $request): ?string
    {
        ob_start();
        $this->app->run($request);
        return ob_get_clean();
    }

    /**
     * @test
     */
    public function GivenApplicationWithController_HandlesGetRequest(): void
    {
        $this->mockContainer->setMapping(EchoController::class, new EchoController());
        $this->app->get(self::ECHO_ENDPOINT, EchoController::class);

        $content = json_encode(self::TEST_PAYLOAD);
        $request = Request::create(self::ECHO_ENDPOINT, Request::METHOD_GET, [], [], [], [], $content);

        $body = $this->runAndGetResponse($request);

        $this->assertEquals($content, $body);
        $this->assertNull($this->error);
    }

    /**
     * @test
     */
    public function GivenApplicationWithController_HandlesPostRequest(): void
    {
        $this->mockContainer->setMapping(EchoController::class, new EchoController());
        $this->app->post(self::ECHO_ENDPOINT, EchoController::class);

        $content = json_encode(self::TEST_PAYLOAD);
        $request = Request::create(self::ECHO_ENDPOINT, Request::METHOD_POST, [], [], [], [], $content);

        $body = $this->runAndGetResponse($request);

        $this->assertEquals($content, $body);
        $this->assertNull($this->error);
    }

    /**
     * @test
     */
    public function GivenApplicationWithController_HandlesPutRequest(): void
    {
        $this->mockContainer->setMapping(EchoController::class, new EchoController());
        $this->app->put(self::ECHO_ENDPOINT, EchoController::class);

        $content = json_encode(self::TEST_PAYLOAD);
        $request = Request::create(self::ECHO_ENDPOINT, Request::METHOD_PUT, [], [], [], [], $content);

        $body = $this->runAndGetResponse($request);

        $this->assertEquals($content, $body);
        $this->assertNull($this->error);
    }

    /**
     * @test
     */
    public function GivenApplicationWithController_HandlesDeleteRequest(): void
    {
        $this->mockContainer->setMapping(EchoController::class, new EchoController());
        $this->app->delete(self::ECHO_ENDPOINT, EchoController::class);

        $content = json_encode(self::TEST_PAYLOAD);
        $request = Request::create(self::ECHO_ENDPOINT, Request::METHOD_DELETE, [], [], [], [], $content);

        $body = $this->runAndGetResponse($request);

        $this->assertEquals($content, $body);
        $this->assertNull($this->error);
    }

    /**
     * @test
     */
    public function GivenApplicationWithController_HandlesPatchRequest(): void
    {
        $this->mockContainer->setMapping(EchoController::class, new EchoController());
        $this->app->patch(self::ECHO_ENDPOINT, EchoController::class);

        $content = json_encode(self::TEST_PAYLOAD);
        $request = Request::create(self::ECHO_ENDPOINT, Request::METHOD_PATCH, [], [], [], [], $content);

        $body = $this->runAndGetResponse($request);

        $this->assertEquals($content, $body);
        $this->assertNull($this->error);
    }

    /**
     * @test
     */
    public function GivenApplicationWithController_HandlesOptionsRequest(): void
    {
        $this->mockContainer->setMapping(EchoController::class, new EchoController());
        $this->app->options(self::ECHO_ENDPOINT, EchoController::class);

        $content = json_encode(self::TEST_PAYLOAD);
        $request = Request::create(self::ECHO_ENDPOINT, Request::METHOD_OPTIONS, [], [], [], [], $content);

        $body = $this->runAndGetResponse($request);

        $this->assertEquals($content, $body);
        $this->assertNull($this->error);
    }

    /**
     * @test
     */
    public function GivenApplicationWithController_WhenNoMethodRestriction_HandlesAllKindOfRequests(): void
    {
        $this->mockContainer->setMapping(EchoController::class, new EchoController());
        $this->app->match(self::ECHO_ENDPOINT, EchoController::class);

        $content = json_encode(self::TEST_PAYLOAD);

        $request = Request::create(self::ECHO_ENDPOINT, Request::METHOD_GET, [], [], [], [], $content);
        $body = $this->runAndGetResponse($request);
        $this->assertEquals($content, $body);
        $this->assertNull($this->error);

        $request = Request::create(self::ECHO_ENDPOINT, Request::METHOD_POST, [], [], [], [], $content);
        $body = $this->runAndGetResponse($request);
        $this->assertEquals($content, $body);
        $this->assertNull($this->error);
    }

    /**
     * @test
     */
    public function GivenApplicationWithController_HandlesErrors(): void
    {
        $this->mockContainer->setMapping(ErrorThrowingController::class, new ErrorThrowingController());
        $this->app->post(self::ERROR_ENDPOINT, ErrorThrowingController::class);

        $content = json_encode(self::TEST_PAYLOAD);
        $request = Request::create(self::ERROR_ENDPOINT, Request::METHOD_POST, [], [], [], [], $content);

        $body = $this->runAndGetResponse($request);

        $this->assertNotEquals($content, $body);
        $this->assertEquals(new \RuntimeException('Test exception'), $this->error);
    }

    /**
     * @test
     */
    public function GivenApplicationWithController_HandlesFinish(): void
    {
        $this->mockContainer->setMapping(EchoController::class, new EchoController());
        $this->app->get(self::ECHO_ENDPOINT, EchoController::class);

        $requestSpy = null;
        $responseSpy = null;
        $this->app->finish(function(Request $request, Response $response) use (&$requestSpy, &$responseSpy) {
            $requestSpy = $request;
            $responseSpy = $response;
        });

        $content = json_encode(self::TEST_PAYLOAD);
        $request = Request::create(self::ECHO_ENDPOINT, Request::METHOD_GET, [], [], [], [], $content);

        $this->runAndGetResponse($request);

        $this->assertNotNull($requestSpy);
        $this->assertNotNull($responseSpy);
    }

    /**
     * @test
     */
    public function WhenBeforeCallbackIsSet_CallbackIsExecutedBeforeController(): void
    {
        $counter = new Counter();
        $this->mockContainer->setMapping(IncreaseCounterController::class, new IncreaseCounterController($counter));
        $this->app->post(self::ECHO_ENDPOINT, IncreaseCounterController::class);
        $this->app->before(function() use ($counter) {
            Assert::assertSame(0, $counter->get());
            $counter->increaseByOne();
        });

        $content = json_encode(self::TEST_PAYLOAD);
        $request = Request::create(self::ECHO_ENDPOINT, Request::METHOD_POST, [], [], [], [], $content);

        $this->runAndGetResponse($request);

        $this->assertEquals(2, $counter->get());
    }

    /**
     * @test
     */
    public function WhenBeforeSpecificControllerCallbackIsSet_CallbackIsExecutedBeforeControllerAndAfterGlobalBeforeCallback(): void
    {
        $counter = new Counter();
        $this->mockContainer->setMapping(IncreaseCounterController::class, new IncreaseCounterController($counter));
        $route = $this->app->post(self::ECHO_ENDPOINT, IncreaseCounterController::class);
        $this->app->before(function() use ($counter) {
            Assert::assertSame(0, $counter->get());
            $counter->increaseByOne();
        });
        $route->before(function() use ($counter) {
            Assert::assertSame(1, $counter->get());
            $counter->increaseByOne();
        });

        $content = json_encode(self::TEST_PAYLOAD);
        $request = Request::create(self::ECHO_ENDPOINT, Request::METHOD_POST, [], [], [], [], $content);

        $this->runAndGetResponse($request);

        $this->assertEquals(3, $counter->get());
    }

    /**
     * @test
     */
    public function WhenAfterCallbackIsSet_CallbackIsExecutedAfterController(): void
    {
        $counter = new Counter();
        $this->mockContainer->setMapping(IncreaseCounterController::class, new IncreaseCounterController($counter));
        $this->app->post(self::ECHO_ENDPOINT, IncreaseCounterController::class);
        $this->app->after(function() use ($counter) {
            Assert::assertSame(1, $counter->get());
            $counter->increaseByOne();
        });

        $content = json_encode(self::TEST_PAYLOAD);
        $request = Request::create(self::ECHO_ENDPOINT, Request::METHOD_POST, [], [], [], [], $content);

        $this->runAndGetResponse($request);

        $this->assertEquals(2, $counter->get());
    }

    /**
     * @test
     */
    public function WhenAfterSpecificControllerCallbackIsSet_CallbackIsExecutedAfterControllerAndBeforeGlobalAfterCallback(): void
    {
        $counter = new Counter();
        $this->mockContainer->setMapping(IncreaseCounterController::class, new IncreaseCounterController($counter));
        $route = $this->app->post(self::ECHO_ENDPOINT, IncreaseCounterController::class);
        $this->app->after(function() use ($counter) {
            Assert::assertSame(2, $counter->get());
            $counter->increaseByOne();
        });
        $route->after(function() use ($counter) {
            Assert::assertSame(1, $counter->get());
            $counter->increaseByOne();
        });

        $content = json_encode(self::TEST_PAYLOAD);
        $request = Request::create(self::ECHO_ENDPOINT, Request::METHOD_POST, [], [], [], [], $content);

        $this->runAndGetResponse($request);

        $this->assertEquals(3, $counter->get());
    }

    /**
     * @test
     */
    public function GivenMiddleWareCallback_WhenAfterCallbackIsSet_ExpectNoError(): void
    {
        $this->expectNotToPerformAssertions();
        $this->mockContainer->setMapping(EndpointDummy::class, new EndpointDummy());
        $this->mockContainer->setMapping(MiddlewareDummy::class, new MiddlewareDummy());
        $this->app->post(self::ECHO_ENDPOINT, EndpointDummy::class);
        $this->app->after(MiddlewareDummy::class);

        $content = json_encode(self::TEST_PAYLOAD);
        $request = Request::create(self::ECHO_ENDPOINT, Request::METHOD_POST, [], [], [], [], $content);

        $this->runAndGetResponse($request);
    }
}
