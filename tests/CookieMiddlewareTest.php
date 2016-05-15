<?php
/**
 * Created by PhpStorm.
 * User: skebix
 * Date: 15/05/16
 * Time: 02:53 PM
 */

namespace tests;

use app\middleware\CookieMiddleware;
use Dotenv\Dotenv;
use Slim\Http\Cookies;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

class CookieMiddlewareTest extends \PHPUnit_Framework_TestCase
{

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
        $this->response = new Response();

        $this->next = $this->getMockBuilder('app\middleware\ConfigMiddleware')
            ->setConstructorArgs(array($this->dotenv))
            ->setMethods(array('__invoke'))
            ->getMock();
    }

    public function testValidCookieReturnsValidResponse()
    {
        $cookies = new Cookies($this->request->getCookieParams());
        $action = new CookieMiddleware($cookies);

        $this->next->expects($this->once())
            ->method('__invoke')
            ->will($this->returnValue("skebix"));

        // run the controller action and test it
        $response = $action($this->request, $this->response, $this->next);
        $expectedBody = "skebix";

        $this->assertSame($expectedBody, $response);
    }

    public function testWithoutCookieReturnsErrorResponse()
    {
        $cookies = new Cookies();
        $action = new CookieMiddleware($cookies);

        $response = $action($this->request, $this->response, $this->next);

        $expectedBody = json_encode(['error' => true, 'message' => 'Not a valid session.']);
        $expectedStatusCode = 403;

        $body = (string) $response->getBody();
        $statusCode = $response->getStatusCode();

        $this->assertSame($expectedBody, $body);
        $this->assertSame($expectedStatusCode, $statusCode);
    }
}