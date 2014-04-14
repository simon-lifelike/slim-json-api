<?php
namespace JsonApi;

/**
 * jsonAPI - Slim extension to implement fast JSON API's
 *
 * @package Slim
 * @subpackage Middleware
 * @author Jonathan Tavares <the.entomb@gmail.com>
 * @license GNU General Public License, version 3
 * @filesource
 *
 *
*/

use \Slim\Middleware;
use \Slim\Slim;

/**
 * JsonApiMiddleware - Middleware that sets a bunch of static routes for easy bootstrapping of json API's
 *
 * @package Slim
 * @subpackage View
 * @author Jonathan Tavares <the.entomb@gmail.com>
 * @license GNU General Public License, version 3
 * @filesource
 */
class JsonMiddleware extends Middleware {


    /**
     * Sets a buch of static API calls
     *
     */
    function __construct(){

        $app = Slim::getInstance();
        $app->config('debug', false);

        // Mirrors the API request
        $app->get('/return', function() use ($app) {

            $app->render(200, array(
                'method'    => $app->request()->getMethod(),
                'name'      => $app->request()->get('name'),
                'headers'   => $app->request()->headers(),
                'params'    => $app->request()->params(),
            ));
        });

        // Generic error handler
        $app->error(function (\Exception $e) use ($app) {

            $error_type = self::_errorType($e->getCode());
            $error_msg = $e->getMessage();

            $app->render(500, array(
                'sys_response' => 'Server Error',
                'success'      => 0,
                'error'        => "$error_type: $error_msg",
            ));
        });

        // Not found handler (invalid routes, invalid method types)
        $app->notFound(function() use ($app) {
            $app->render(404, array(
                'sys_response' => 'Error',
                'success'      => 0,
                'error'        => 'Invalid route',
            ));
        });

        // Handle Empty response body
        $app->hook('slim.after.router', function () use ($app) {

            //Fix sugested by: https://github.com/bdpsoft
            //Will allow download request to flow
            $content_type = $app->response()->header('Content-Type');
            if($content_type == 'application/octet-stream') return;

            if (strlen($app->response()->body()) == 0) {
                $app->render(500, array(
                    'sys_response' => 'Error',
                    'success'      => 0,
                    'error'        => 'Empty response',
                ));
            }
        });

    }

    /**
     * Call next
     */
    function call(){
        return $this->next->call();
    }

    static function _errorType($type=1){
        switch($type)
        {
            default:
            case E_ERROR: // 1 //
                return 'ERROR';
            case E_WARNING: // 2 //
                return 'WARNING';
            case E_PARSE: // 4 //
                return 'PARSE';
            case E_NOTICE: // 8 //
                return 'NOTICE';
            case E_CORE_ERROR: // 16 //
                return 'CORE_ERROR';
            case E_CORE_WARNING: // 32 //
                return 'CORE_WARNING';
            case E_CORE_ERROR: // 64 //
                return 'COMPILE_ERROR';
            case E_CORE_WARNING: // 128 //
                return 'COMPILE_WARNING';
            case E_USER_ERROR: // 256 //
                return 'USER_ERROR';
            case E_USER_WARNING: // 512 //
                return 'USER_WARNING';
            case E_USER_NOTICE: // 1024 //
                return 'USER_NOTICE';
            case E_STRICT: // 2048 //
                return 'STRICT';
            case E_RECOVERABLE_ERROR: // 4096 //
                return 'RECOVERABLE_ERROR';
            case E_DEPRECATED: // 8192 //
                return 'DEPRECATED';
            case E_USER_DEPRECATED: // 16384 //
                return 'USER_DEPRECATED';
        }
    }

}
