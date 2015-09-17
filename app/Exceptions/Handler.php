<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $e
     * @return void
     */
    public function report(Exception $e)
    {
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        //如果是自定义的异常，则返回上一页操作
        if ($e instanceof CommonException) {
            return redirect()->back()->withErrors($e->getMessage())->withInput();
        } elseif ($this->isHttpException($e)) {
            //如果是HttpException,则返回错误页面
            abort(404);
        } else {
            //如果是其他，则返回默认页面
            return parent::render($request, $e);
        }
    }
}
