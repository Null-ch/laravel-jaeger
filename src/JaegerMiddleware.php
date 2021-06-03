<?php

declare(strict_types=1);

namespace Chocofamilyme\LaravelJaeger;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Closure;

final class JaegerMiddleware
{
    private Jaeger $jaeger;

    public function __construct(Jaeger $jaeger)
    {
        $this->jaeger = $jaeger;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $httpMethod = $request->method();
        $url = $request->path();

        $headers = [];

        foreach ($request->headers->all() as $key => $value) {
            $headers[$key] = Arr::first($value);
        }

        $jaeger = $this->jaeger;

        $jaeger->initServerContext($headers);
        $jaeger->start("$httpMethod: /$url", [
            'http.scheme' => $request->getScheme(),
            'http.ip_address' => $request->ip(),
            'http.host' => $request->getHost(),
            'laravel.version' => app()->version(),
        ]);

        return $next($request);
    }
}