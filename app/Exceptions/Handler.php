<?php

namespace App\Exceptions;

use App\Traits\ErrorLogsTrait;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ErrorLogsTrait;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param \Throwable $exception
     * @return void
     *
     * @throws \Throwable
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof UnauthorizedException) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            // In test/minimal bootstrap contexts the error view stack can query business settings.
            // Return a plain 403 fallback when those tables are unavailable.
            if (app()->runningUnitTests() || !Schema::hasTable('business_settings')) {
                return response('Forbidden', 403);
            }
        }

        if ($this->isHttpException($exception) && $exception?->getStatusCode() == 404) {
            $redirectUrl = $this->storeErrorLogsUrl(url: $request->fullUrl(), statusCode: $exception->getStatusCode());
            if ($redirectUrl && isset($redirectUrl['redirect_url'])) {
                return redirect(to: $redirectUrl['redirect_url'], status: ($redirectUrl['redirect_status'] ?? '301'));
            }
        }
        return parent::render($request, $exception);
    }

    
}
