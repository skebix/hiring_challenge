<?php
/**
 * Created by PhpStorm.
 * User: skebix
 * Date: 15/05/16
 * Time: 03:19 PM
 */

namespace tests;

use app\action\EndpointAction;
use app\domain\chat\FriendsListHandler;
use Dotenv\Dotenv;
use Redis;
use Slim\Http\Cookies;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

class EndpointActionTest extends \PHPUnit_Framework_TestCase
{

    protected $cookies;
    protected $dotenv;
    protected $environment;
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
    }

    public function testValidRequestReturnsSuccess()
    {
        $redis = new Redis();
        $friendsListHandler = new FriendsListHandler($this->cookies, $redis, $this->response);

        $action = new EndpointAction($this->cookies, $this->dotenv, $friendsListHandler, $redis);

        $response = $action($this->request, $this->response, []);

        $expectedBody = json_encode([
            [
                'id' => 1,
                'name' => 'Project 1',
                'threads' => [
                    [
                        'online' => true,
                        'other_party' => [
                            'user_id' => 176733,
                        ]
                    ]
                ]
            ],
            [
                'id' => 2,
                'name' => 'Project 2',
                'threads' => [
                    [
                        'online' => true,
                        'other_party' => [
                            'user_id' => 176733,
                        ]
                    ]
                ]
            ]
        ]);
        $expectedStatusCode = 200;

        $body = (string) $response->getBody();
        $statusCode = $response->getStatusCode();

        $this->assertSame($expectedBody, $body);
        $this->assertSame($expectedStatusCode, $statusCode);
    }

    public function testRedisDisconnected()
    {
        $redis = $this->getMockBuilder('\Redis')
            ->setMethods(array('isConnected'))
            ->getMock();

        $redis->expects($this->once())
            ->method('isConnected')
            ->will($this->returnValue(false));

        $friendsListHandler = new FriendsListHandler($this->cookies, $redis, $this->response);

        $action = new EndpointAction($this->cookies, $this->dotenv, $friendsListHandler, $redis);

        $response = $action($this->request, $this->response, []);

        $expectedBody = json_encode(['error' => true, 'message' => 'Server error, can\'t connect.']);
        $expectedStatusCode = 500;

        $body = (string) $response->getBody();
        $statusCode = $response->getStatusCode();

        $this->assertSame($expectedBody, $body);
        $this->assertSame($expectedStatusCode, $statusCode);
    }

    public function testSerializedFriendsListReturnsResponseInstance()
    {
        $redis = $this->getMockBuilder('\Redis')
            ->setMethods(array('get'))
            ->getMock();

        $redis->expects($this->at(0))
            ->method('get')
            ->will($this->returnValue(['default' => ['id' => 1]]));
        $redis->expects($this->at(1))
            ->method('get')
            ->will($this->returnValue(false));

        $friendsListHandler = new FriendsListHandler($this->cookies, $redis, $this->response);

        $action = new EndpointAction($this->cookies, $this->dotenv, $friendsListHandler, $redis);

        $response = $action($this->request, $this->response, []);

        $expectedBody = json_encode([]);
        $expectedStatusCode = 200;

        $body = (string) $response->getBody();
        $statusCode = $response->getStatusCode();

        $this->assertSame($expectedBody, $body);
        $this->assertSame($expectedStatusCode, $statusCode);
    }

    public function testRedissetOptionThrowsException()
    {
        $redis = $this->getMockBuilder('\Redis')
            ->setMethods(array('setOption'))
            ->getMock();

        $redis->expects($this->once())
            ->method('setOption')
            ->will($this->throwException(new \Exception('Varied message here')));

        $friendsListHandler = new FriendsListHandler($this->cookies, $redis, $this->response);

        $action = new EndpointAction($this->cookies, $this->dotenv, $friendsListHandler, $redis);

        $response = $action($this->request, $this->response, []);

        $expectedBody = json_encode(['error' => true, 'message' => 'Unknown exception. Varied message here']);
        $expectedStatusCode = 500;

        $body = (string) $response->getBody();
        $statusCode = $response->getStatusCode();

        $this->assertSame($expectedBody, $body);
        $this->assertSame($expectedStatusCode, $statusCode);
    }
}