<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof HttpException) { // HTTP异常
            switch ($e->getStatusCode()) {
                case 404:
                    return hError('接口未找到');
                default:
                    break;
            }
        } elseif ($e instanceof ValidationException) { // 数据验证异常
            $errors = json_decode($e->getResponse()->getContent(), true);
            if (empty($errors)) {
                return hError('数据验证异常');
            }
            $first_value = current(array_values($errors))[0];
            return hError($first_value);
        } elseif ($e instanceof  KnownLogicException) {
            //已知的逻辑错误，直接抛出message
            return hError($e->getMessage());
        } elseif ($e instanceof  ApiParaException) {
            //已知的参数错误，直接抛出message
            return hError($e->getMessage());
        } else {
            if (!hIsTestEnv()) {
                hFormatException($e, true);
                return hError('当前服务不可用，请联系管理员!');
            }
        }
        return parent::render($request, $e);
    }
}
