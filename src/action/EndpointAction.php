<?php
/**
 * Created by PhpStorm.
 * User: skebix
 * Date: 15/05/16
 * Time: 01:24 PM
 */

namespace app\action;

use app\domain\chat\FriendsList;
use app\domain\chat\FriendsListHandler;
use Dotenv\Dotenv;
use Redis;
use Slim\Http\Cookies;
use Slim\Http\Request;
use Slim\Http\Response;

class EndpointAction
{

    protected $cookie;
    protected $dotenv;
    protected $friendsListHandler;
    protected $redis;

    public function __construct(Cookies $cookie, Dotenv $dotenv, FriendsListHandler $friendsListHandler, Redis $redis)
    {
        $this->cookie = $cookie;
        $this->dotenv = $dotenv;
        $this->friendsListHandler = $friendsListHandler;
        $this->redis = $redis;
    }

    public function __invoke(Request $request, Response $response, $args = [])
    {
        try {
            $this->dotenv->load();

            $redisHost = getenv('REDIS_HOST');
            $redisPort = getenv('REDIS_PORT');

            $redis = $this->redis;
            $redis->connect($redisHost, $redisPort);

            if (!$redis->isConnected()) {
                $response = $response->withJson(['error' => true, 'message' => 'Server error, can\'t connect.'], 500);
                return $response;
            }

            $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);

            $response = $response->withoutHeader('Set-Cookie');

            $friendsListHandler = $this->friendsListHandler;
            $friendsList = $friendsListHandler->getSerializedFriendsList();

            if ($friendsList instanceof FriendsList) {
                $friendsList = $friendsListHandler->setFriendsOnline($friendsList);
            } else {
                return $friendsList;
            }

            $response = $response->withJson($friendsList->toArray());
        } catch (\Exception $e) {
            $data = ['error' => true, 'message' => 'Unknown exception. ' . $e->getMessage()];
            $response = $response->withJson($data, 500);
        }

        return $response;
    }
}
