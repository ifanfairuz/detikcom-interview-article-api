<?php

if (!defined('BASE_DIR')) define('BASE_DIR', preg_replace('/\/core$/s', "", __DIR__));

/**
 * parse ini file configuration
 * 
 * @var array|false $__ENV
 */
$__ENV = parse_ini_file(BASE_DIR . DIRECTORY_SEPARATOR . 'env.ini');

/**
 * configure ini setting
 */
ini_set('display_errors', (@$__ENV['env'] ?? 'development') === 'production' ? 0 : 1);
ini_set('error_log', join(DIRECTORY_SEPARATOR, [BASE_DIR, 'log', 'error-' . date('Y-m-d') . '.log']));

/**
 * Get enviroment variable from ini file
 *
 * @param string $key key for configuration env
 * @param string $default default value if key doesn't not exist
 * @return string
 */
function env($key, $default = '')
{
    global $__ENV;

    return trim(@$__ENV[$key] ?? $default);
}

/**
 * load config
 * 
 * @var array $__CONFIG
 */
$__CONFIG = include(join(DIRECTORY_SEPARATOR, [BASE_DIR, 'config', 'config.php']));

require_once(__DIR__ . "/helpers.php");
require_once(__DIR__ . "/autoload.php");

use App\Command\CommandHandler;
use Core\Http\Exception\HttpException;
use Core\Http\Request;
use Core\Http\Response;
use Core\Http\Router;

function handle_request()
{
    try {
        $req = new Request();
        $req->validate();
        $req->parseBody();
        $route = include(app_dir('route.php'));
        $router = new Router($route);
        $router->handle($req);
    } catch (HttpException $e) {
        error_log($e->getMessage());
        Response::error($req, $e)->render();
    } catch (Exception $e) {
        error_log($e->getMessage());
        Response::error($req, new HttpException())->render();
    }
}

function handle_command($argc, $argv)
{
    array_shift($argv);

    $handler = new CommandHandler($argv);
    $handler->handle();
}
