<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

require_once __DIR__.'/../vendor/autoload.php';

require_once __DIR__ . '/../.env.php';

// 引入全局使用的自定义函数
require_once __DIR__ . '/../app/Common/Helpers.php';

$app = new Laravel\Lumen\Application(
    realpath(__DIR__.'/../')
);

$env_filename = '.env_' . _ENV_FILE_PATH_;
if (!is_file(__DIR__ . '/../' . $env_filename)) {
    die('env file not found');
}

try {
    (new Dotenv\Dotenv(__DIR__.'/../', $env_filename))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    //
}


$app->withFacades();

$app->withEloquent();


/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/
// 全局中间件
// $app->middleware([
//    App\Http\Middleware\ExampleMiddleware::class
// ]);

$app->routeMiddleware([
    'verify' => App\Http\Middleware\VerifyMiddleware::class, // 请求验证中间件
    'widget' => App\Http\Middleware\WidgetMiddleware::class, // 挂件中间件
    'additional' => App\Http\Middleware\AdditionalMiddleware::class, // 附加中间件
//     'auth' => App\Http\Middleware\Authenticate::class,
    'actionlog' => App\Http\Middleware\ActionLogMiddleware::class,
    'permission' => App\Http\Middleware\PermissionMiddleware::class,
    'direct'=>App\Http\Middleware\DirectVerifyMiddleware::class,
    'menubadge'=>App\Http\Middleware\MenuBadgeMiddleware::class,
    'internalapi'=>App\Http\Middleware\InternalApiLogMiddleware::class,
    'fakedata'=>App\Http\Middleware\FakeDataMiddleware::class,
    'mobileapi'=>App\Http\Middleware\MobileApiMiddleware::class, //手机端验证中间件
    'crossdomain'=>App\Http\Middleware\CrossDomainMiddleware::class, //测试环境允许跨域
]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/
$app->register(App\Providers\XLSXWriterServiceProvider::class);  // 注册XLSX导出服务提供者
// $app->register(App\Providers\AppServiceProvider::class);
// $app->register(App\Providers\AuthServiceProvider::class);
// $app->register(App\Providers\EventServiceProvider::class);

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    //动态require 路由文件
    $routes = glob(__DIR__.'/../routes/*.php');
    foreach ($routes as $file) {
        require $file;
    }
});

return $app;
