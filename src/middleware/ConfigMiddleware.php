<?php
/**
 * Created by PhpStorm.
 * User: skebix
 * Date: 15/05/16
 * Time: 12:18 PM
 */

namespace app\middleware;

use Dotenv\Dotenv;
use Slim\Http\Request;
use Slim\Http\Response;

class ConfigMiddleware
{

    protected $dotenv;

    public function __construct(Dotenv $dotenv)
    {
        $this->dotenv = $dotenv;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        $this->dotenv->load();

        $redisHost = getenv('REDIS_HOST');
        $redisPort = getenv('REDIS_PORT');
        $allowedDomains = $this->setAllowedDomains();

        if (empty($redisHost) || empty($redisPort) || empty($allowedDomains) || !is_array($allowedDomains)) {
            $data = ['error' => true, 'message' => 'Server error, invalid configuration.'];
            $response = $response->withJson($data, 500);
        } else {
            $response = $next($request, $response, []);
        }

        return $response;
    }

    public function setAllowedDomains(){
        return explode(',', getenv('ALLOWED_DOMAINS'));
    }
}
