<?php
/**
 * Created by PhpStorm.
 * User: skebix
 * Date: 15/05/16
 * Time: 03:32 PM
 */

namespace tests;

use app\action\EndpointAction;
use app\domain\chat\FriendsListHandler;
use Dotenv\Dotenv;
use Slim\Http\Cookies;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

class FriendsListHandlerTest extends \PHPUnit_Framework_TestCase
{

    public function testSerializedFriendsListReturnsResponseInstance()
    {
        $dotenv = new Dotenv(__DIR__ . '/../');

        $environment = Environment::mock([
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/',
                'HTTP_COOKIE' => 'app=hash',
                'HTTP_ORIGIN' => 'skebix.com.ve'
            ]
        );

        $request = Request::createFromEnvironment($environment);
        $response = new Response();
        $cookies = new Cookies($request->getCookieParams());

        $redis = $this->getMockBuilder('\Redis')
            ->setMethods(array('get'))
            ->getMock();

        $redis->expects($this->once())
            ->method('get')
            ->will($this->returnValue(false));

        $friendsListHandler = new FriendsListHandler($cookies, $redis, $response);

        $action = new EndpointAction($cookies, $dotenv, $friendsListHandler, $redis);

        $response = $action($request, $response, []);

        $expectedBody = json_encode(['error' => true, 'message' => 'Friends list not available.']);
        $expectedStatusCode = 404;

        $body = (string) $response->getBody();
        $statusCode = $response->getStatusCode();

        $this->assertSame($expectedBody, $body);
        $this->assertSame($expectedStatusCode, $statusCode);
    }
}