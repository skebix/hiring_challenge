<?php
/**
 * Created by PhpStorm.
 * User: skebix
 * Date: 15/05/16
 * Time: 03:57 PM
 */

namespace tests;

class IntegrationTest extends \PHPUnit_Framework_TestCase
{
    public function testIntegrationRequiringEndpoint()
    {
        ob_start();
        require __DIR__ . '/../webroot/index.php';
        $response = ob_get_clean();
        $expectedResponse = json_encode(['error' => true, 'message' => 'Not a valid session.']);

        $this->assertEquals($response, $expectedResponse);
    }
}