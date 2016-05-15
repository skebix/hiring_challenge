<?php
/**
 * Created by PhpStorm.
 * User: skebix
 * Date: 15/05/16
 * Time: 01:24 PM
 */

namespace app\action;

use app\domain\chat\FriendsList;
use Dotenv\Dotenv;
use Redis;
use Slim\Http\Cookies;
use Slim\Http\Request;
use Slim\Http\Response;

class EndpointAction
{

    const FRIENDS_CACHE_PREFIX_KEY = 'chat:friends:';
    const ONLINE_CACHE_PREFIX_KEY = 'chat:online:';

    protected $cookie;
    protected $dotenv;
    protected $redis;

    public function __construct(Cookies $cookie, Dotenv $dotenv, Redis $redis)
    {
        $this->cookie = $cookie;
        $this->dotenv = $dotenv;
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

            $friendsList = $this->getSerializedFriendsList($response);

            if($friendsList instanceof FriendsList){
                $friendsList = $this->setFriendsOnline($friendsList);
            }else{
                return $friendsList;
            }

            $response = $response->withJson($friendsList->toArray());

        } catch (\Exception $e) {
            $data = ['error' => true, 'message' => 'Unknown exception. ' . $e->getMessage()];
            $response = $response->withJson($data, 500);
        }

        return $response;
    }

    public function getSerializedFriendsList(Response $response)
    {
        $sessionHash = $this->cookie->get('app');
        $session = $this->redis->get('PHPREDIS_SESSION:' . $sessionHash);

        if (!empty($session['default']['id'])) {
            $friendsList = $this->redis->get(self::FRIENDS_CACHE_PREFIX_KEY . $session['default']['id']);
            if (!$friendsList) {
                $response = $response->withJson([]);
                return $response;
            }
        } else {
            $response = $response->withJson(['error' => true, 'message' => 'Friends list not available.'], 404);
            return $response;
        }

        return $friendsList;
    }

    public function setFriendsOnline(FriendsList $friendsList)
    {
        $friendUserIds = $friendsList->getUserIds();

        if (!empty($friendUserIds)) {
            $keys = array_map(function ($userId) {
                return self::ONLINE_CACHE_PREFIX_KEY . $userId;
            }, $friendUserIds);

            $result = $this->redis->mget($keys);

            $onlineUsers = array_filter(
                array_combine(
                    $friendUserIds,
                    $result
                )
            );

            if ($onlineUsers) {
                $friendsList->setOnline($onlineUsers);
            }
        }
        return $friendsList;
    }
}