<?php
/**
 * Created by PhpStorm.
 * User: skebix
 * Date: 15/05/16
 * Time: 03:07 PM
 */

namespace tests;

use app\middleware\CorsMiddleware;
use Dotenv\Dotenv;
use Slim\Http\Cookies;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

class CorsMiddlewareTest extends \PHPUnit_Framework_TestCase
{

    protected $cookies;
    protected $dotenv;
    protected $environment;
    protected $next;
    protected $request;
    protected $response;

    protected function setUp()
    {
        $this->dotenv = new Dotenv(__DIR__ . '/../');

        $this->environment = Environment::mock([
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/',
                'HTTP_COOKIE' => 'app=hash',
                'HTTP_ORIGIN' => 'skebix.com.ve'
            ]
        );

        $this->request = Request::createFromEnvironment($this->environment);
        $this->cookies = new Cookies($this->request->getCookieParams());
        $this->response = new Response();

        $this->next = $this->getMockBuilder('app\middleware\CookieMiddleware')
            ->setConstructorArgs(array($this->cookies))
            ->setMethods(array('__invoke'))
            ->getMock();
    }

    public function testValidOriginReturnsValidResponse()
    {
        $action = new CorsMiddleware($this->dotenv);

        $this->next->expects($this->once())
            ->method('__invoke')
            ->will($this->returnValue("skebix"));

        $response = $action($this->request, $this->response, $this->next);
        $expectedBody = "skebix";

        $this->assertSame($expectedBody, $response);
    }

    public function testInvalidOriginReturnsErrorResponse()
    {
        $action = $this->getMockBuilder('app\middleware\CorsMiddleware')
            ->setConstructorArgs(array($this->dotenv))
            ->setMethods(array('setAllowBlankReferrer'))
            ->getMock();

        $action->expects($this->once())
            ->method('setAllowBlankReferrer')
            ->will($this->returnValue(false));

        $response = $action->__invoke($this->request, $this->response, $this->next);

        $expectedBody = json_encode(['error' => true, 'message' => 'Not a valid origin.']);
        $expectedStatusCode = 403;

        $body = (string) $response->getBody();
        $statusCode = $response->getStatusCode();

        $this->assertSame($expectedBody, $body);
        $this->assertSame($expectedStatusCode, $statusCode);
    }
}