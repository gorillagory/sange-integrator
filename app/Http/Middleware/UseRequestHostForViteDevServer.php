<?php

namespace App\Http\Middleware;

use App\Support\AppHost;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class UseRequestHostForViteDevServer
{
    public function handle(Request $request, Closure $next): Response
    {
        $defaultHotFile = public_path('hot');

        if (! is_file($defaultHotFile)) {
            return $next($request);
        }

        $hotOrigin = trim((string) file_get_contents($defaultHotFile));
        $requestAwareOrigin = $this->requestAwareOrigin($request, $hotOrigin);
        $requestHotFile = $this->hotFilePath($requestAwareOrigin);

        if (! is_file($requestHotFile) || trim((string) file_get_contents($requestHotFile)) !== $requestAwareOrigin) {
            $directory = dirname($requestHotFile);

            if (! is_dir($directory)) {
                mkdir($directory, 0777, true);
            }

            file_put_contents($requestHotFile, $requestAwareOrigin, LOCK_EX);
        }

        Vite::useHotFile($requestHotFile);

        return $next($request);
    }

    private function requestAwareOrigin(Request $request, string $hotOrigin): string
    {
        $parsedOrigin = parse_url(rtrim($hotOrigin, '/')) ?: [];
        $scheme = (string) ($parsedOrigin['scheme'] ?? 'http');
        $port = isset($parsedOrigin['port']) ? (int) $parsedOrigin['port'] : 5173;
        $path = (string) ($parsedOrigin['path'] ?? '');
        $host = AppHost::formatHostForUrl((string) $request->getHost());

        $origin = $scheme.'://'.$host;

        if ($port > 0) {
            $origin .= ':'.$port;
        }

        if ($path !== '' && $path !== '/') {
            $origin .= '/'.ltrim($path, '/');
        }

        return $origin;
    }

    private function hotFilePath(string $origin): string
    {
        return storage_path('framework/cache/vite-hot-'.md5($origin).'.hot');
    }
}
