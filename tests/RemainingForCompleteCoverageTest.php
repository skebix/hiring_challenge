<?php
/**
 * Created by PhpStorm.
 * User: skebix
 * Date: 15/05/16
 * Time: 04:01 PM
 */

namespace tests;

use Redis;
use app\domain\chat\FriendsListHandler;
use app\action\EndpointAction;

class RemainingForCompleteCoverageTest extends \PHPUnit_Framework_TestCase
{
    public function testContainerInstances()
    {
        ob_start();
        require __DIR__ . '/../webroot/index.php';

        $redis = $app->getContainer()->get('redis');
        $friendsListHandler = $app->getContainer()->get('app\domain\chat\FriendsListHandler');
        $endpointAction = $app->getContainer()->get('app\action\EndpointAction');

        $response = ob_get_clean();

        $this->assertInstanceOf(Redis::class, $redis);
        $this->assertInstanceOf(FriendsListHandler::class, $friendsListHandler);
        $this->assertInstanceOf(EndpointAction::class, $endpointAction);
    }
}