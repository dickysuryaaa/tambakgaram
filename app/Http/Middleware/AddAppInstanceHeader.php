<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menyisipkan hostname pod/container pada setiap response untuk
 * memverifikasi distribusi beban dan perpindahan trafik saat gangguan.
 */
class AddAppInstanceHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $response->headers->set('X-App-Instance', gethostname() ?: 'unknown');

        return $response;
    }
}
