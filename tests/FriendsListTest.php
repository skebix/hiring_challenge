<?php
/**
 * Created by PhpStorm.
 * User: skebix
 * Date: 15/05/16
 * Time: 03:47 PM
 */

namespace tests;

use app\domain\chat\FriendsList;

class FriendListTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @param FriendsList $data Friends list to be used
     * @param FriendsList $expectedResult Boolean
     *
     * @dataProvider providerForIsEmpty
     */
    public function testIsEmpty($data, $expectedResult)
    {
        $friendsList = new FriendsList($data);
        $result = $friendsList->isEmpty();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param FriendsList $data Friend list to be used
     * @param FriendsList $expectedResult Array of User IDs
     *
     * @dataProvider providerForGetUserIds
     */
    public function testGetUserIds($data, $expectedResult)
    {
        $friendsList = new FriendsList($data);
        $result = $friendsList->getUserIds();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param FriendsList $data Friend list to be used
     * @param FriendsList $expectedResult JSON encoded data
     *
     * @dataProvider providerForToJson
     */
    public function testToJson($data, $expectedResult)
    {
        $friendsList = new FriendsList($data);
        $result = $friendsList->toJson();

        $this->assertJsonStringEqualsJsonString($expectedResult, $result);
    }

    /**
     * @param FriendsList $data Friend list to be used
     * @param FriendsList $expectedResult Array data
     *
     * @dataProvider providerForToArray
     */
    public function testToArray($data, $expectedResult)
    {
        $friendsList = new FriendsList($data);
        $result = $friendsList->toArray();

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param FriendsList $data Friend list to be used
     * @param array $onlineUsers Data with users offline
     *
     * @dataProvider providerForSetOnline
     */
    public function testSetOnline($data, $onlineUsers)
    {
        $friendsList = new FriendsList($data);
        $friendsList->setOnline($onlineUsers);

        $friendsListData = $friendsList->toArray();

        foreach ($friendsListData as $projectIndex => $projects) {
            foreach ($projects['threads'] as $threadIndex => $thread) {
                if (in_array($thread['other_party']['user_id'], $onlineUsers)) {
                    $this->assertTrue($friendsListData[$projectIndex]['threads'][$threadIndex]['online']);
                }
            }
        }
    }

    // @codeCoverageIgnoreStart
    public function getFilledFriendsListData(){
        $data = [
            [
                'id' => 1,
                'name' => 'Project 1',
                'threads' => [
                    [
                        'online' => false,
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
                        'online' => false,
                        'other_party' => [
                            'user_id' => 176733,
                        ]
                    ]
                ]
            ]
        ];

        return $data;
    }

    public function getEmptyFriendsListData(){
        return [];
    }

    public function providerForIsEmpty()
    {
        $empty = $this->getEmptyFriendsListData();
        $filled = $this->getFilledFriendsListData();

        return array(
            array($empty, true),
            array($filled, false)
        );
    }

    public function providerForGetUserIds(){
        $empty = $this->getEmptyFriendsListData();
        $filled = $this->getFilledFriendsListData();

        return array(
            array($empty, []),
            array($filled, [176733])
        );
    }

    public function providerForToJson(){
        $empty = $this->getEmptyFriendsListData();
        $filled = $this->getFilledFriendsListData();

        return array(
            array($empty, json_encode($empty)),
            array($filled, json_encode($filled))
        );
    }

    public function providerForToArray(){
        $empty = $this->getEmptyFriendsListData();
        $filled = $this->getFilledFriendsListData();

        return array(
            array($empty, $empty),
            array($filled, $filled)
        );
    }

    public function providerForSetOnline(){
        $empty = $this->getEmptyFriendsListData();
        $filled = $this->getFilledFriendsListData();

        return array(
            array($empty, []),
            array($filled, array(176733 => true)),
            array($empty, array(176733 => true)),
            array($filled, []),
        );
    }
    // @codeCoverageIgnoreEnd
}