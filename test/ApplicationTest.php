<?php

declare(strict_types=1);

namespace Clearbooks\Dilex;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class ApplicationTest extends TestCase
{
    private const TEST_PAYLOAD = [
        'test1' => 'value1',
        'test2' => 'value2'
    ];

    private const ECHO_ENDPOINT = '/echo';
    private const ERROR_ENDPOINT = '/error';

    private $error;
    private $app;

    public function setUp(): void
    {
        parent::setUp();

        $this->error = null;
        $container = new MockContainer( [
                EchoController::class => new EchoController(),
                ErrorThrowingController::class => new ErrorThrowingController()
        ] );
        $this->app = new Dilex( 'test', true, $container );
        $this->app->post(self::ECHO_ENDPOINT, EchoController::class);
        $this->app->post(self::ERROR_ENDPOINT, ErrorThrowingController::class);

        $this->app->error(function ($e) {
            $this->error = $e;
        });
    }

    /**
     * @test
     */
    public function givenApplicationWithController_handlesRequest(): void
    {
        $content = json_encode(self::TEST_PAYLOAD);

        $request = Request::create(self::ECHO_ENDPOINT, Request::METHOD_POST, [], [], [], [], $content);

        ob_start();
        $this->app->run($request);
        $body = ob_get_clean();

        $this->assertEquals($content, $body);
        $this->assertNull($this->error);
    }

    /**
     * @test
     */
    public function givenApplicationWithController_handlesErrors(): void
    {
        $content = json_encode(self::TEST_PAYLOAD);

        $request = Request::create(self::ERROR_ENDPOINT, Request::METHOD_POST, [], [], [], [], $content);

        ob_start();
        $this->app->run($request);
        $body = ob_get_clean();

        $this->assertNotEquals($content, $body);
        $this->assertNotNull($this->error);
    }
}
