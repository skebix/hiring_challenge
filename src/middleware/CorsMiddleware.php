<?php
/**
 * Created by PhpStorm.
 * User: skebix
 * Date: 15/05/16
 * Time: 12:35 PM
 */

namespace app\middleware;

use Dotenv\Dotenv;
use Slim\Http\Request;
use Slim\Http\Response;

class CorsMiddleware
{

    protected $dotenv;

    public function __construct(Dotenv $dotenv)
    {
        $this->dotenv = $dotenv;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        $this->dotenv->load();

        $allowedDomains = $this->setAllowedDomains();
        $allowBlankReferrer = $this->setAllowBlankReferrer();

        $httpOrigin = $this->setHttpOrigin($request);
        if ($allowBlankReferrer || in_array($httpOrigin, $allowedDomains)) {
            $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
            if ($httpOrigin) {
                $response = $response->withHeader('Access-Control-Allow-Origin', $httpOrigin);
            }
            $response = $next($request, $response, []);
        } else {
            $response = $response->withJson(['error' => true, 'message' => 'Not a valid origin.'], 403);
        }

        return $response;
    }

    public function setAllowedDomains(){
        return explode(',', getenv('ALLOWED_DOMAINS'));
    }

    public function setAllowBlankReferrer(){
        return filter_var(getenv('ALLOW_BLANK_REFERRER'), FILTER_VALIDATE_BOOLEAN);
    }

    public function setHttpOrigin(Request $request){
        $serverParams = $request->getServerParams();
        return !empty($serverParams['HTTP_ORIGIN']) ? $serverParams['HTTP_ORIGIN'] : null;
    }
}