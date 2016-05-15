<?php
/**
 * Created by PhpStorm.
 * User: skebix
 * Date: 15/05/16
 * Time: 12:57 PM
 */

namespace app\middleware;

use Slim\Http\Cookies;
use Slim\Http\Request;
use Slim\Http\Response;

class CookieMiddleware
{

    protected $cookie;

    public function __construct(Cookies $cookie)
    {
        $this->cookie = $cookie;
    }

    public function __invoke(Request $request, Response $response, $next)
    {
        $request_cookie = $this->cookie->get('app');
        if (empty($request_cookie)) {
            $response = $response->withJson(['error' => true, 'message' => 'Not a valid session.'], 403);
        } else {
            $response = $next($request, $response, []);
        }

        return $response;
    }
}