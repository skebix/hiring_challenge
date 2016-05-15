<?php
/**
 * Created by PhpStorm.
 * User: skebix
 * Date: 15/05/16
 * Time: 02:26 PM
 */

namespace tests;

use app\domain\chat\FriendsListHandler;
use app\middleware\ConfigMiddleware;
use Dotenv\Dotenv;
use Redis;
use Slim\Http\Cookies;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

class ConfigMiddlewareTest extends \PHPUnit_Framework_TestCase
{

    protected $cookies;
    protected $dotenv;
    protected $environment;
    protected $friendsListHandler;
    protected $next;
    protected $redis;
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
        $this->redis = new Redis();
        $this->response = new Response();
        $this->friendsListHandler = new FriendsListHandler($this->cookies, $this->redis, $this->response);

        $this->next = $this->getMockBuilder('app\action\EndpointAction')
            ->setConstructorArgs(array($this->cookies, $this->dotenv, $this->friendsListHandler, $this->redis))
            ->getMock();
    }

    public function testValidConfigurationReturnsValidResponse()
    {
        $action = new ConfigMiddleware($this->dotenv);

        $this->next->expects($this->once())
            ->method('__invoke')
            ->will($this->returnValue("skebix"));

        $response = $action($this->request, $this->response, $this->next);
        $expectedBody = "skebix";

        $this->assertSame($expectedBody, $response);
    }

    public function testInvalidConfigurationReturnsErrorResponse()
    {
        $action = $this->getMockBuilder('app\middleware\ConfigMiddleware')
            ->setConstructorArgs(array($this->dotenv))
            ->setMethods(array('setAllowedDomains'))
            ->getMock();

        $action->expects($this->once())
            ->method('setAllowedDomains')
            ->will($this->returnValue(array()));

        $response = $action->__invoke($this->request, $this->response, $this->next);

        $expectedBody = json_encode(['error' => true, 'message' => 'Server error, invalid configuration.']);
        $expectedStatusCode = 500;

        $body = (string) $response->getBody();
        $statusCode = $response->getStatusCode();

        $this->assertSame($expectedBody, $body);
        $this->assertSame($expectedStatusCode, $statusCode);
    }
}