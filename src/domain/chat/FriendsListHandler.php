<?php
/**
 * Created by PhpStorm.
 * User: skebix
 * Date: 15/05/16
 * Time: 01:54 PM
 */

namespace app\domain\chat;

use Redis;
use Slim\Http\Cookies;
use Slim\Http\Response;

class FriendsListHandler
{

    const FRIENDS_CACHE_PREFIX_KEY = 'chat:friends:';
    const ONLINE_CACHE_PREFIX_KEY = 'chat:online:';

    protected $cookie;
    protected $redis;
    protected $response;

    public function __construct(Cookies $cookies, Redis $redis, Response $response)
    {
        $this->cookie = $cookies;
        $this->redis = $redis;
        $this->response = $response;
    }

    public function getSerializedFriendsList()
    {

        $sessionHash = $this->cookie->get('app');
        $session = $this->redis->get('PHPREDIS_SESSION:' . $sessionHash);

        if (!empty($session['default']['id'])) {
            $friendsList = $this->redis->get(self::FRIENDS_CACHE_PREFIX_KEY . $session['default']['id']);
            if (!$friendsList) {
                $response = $this->response->withJson([]);
                return $response;
            }
        } else {
            $response = $this->response->withJson(['error' => true, 'message' => 'Friends list not available.'], 404);
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
