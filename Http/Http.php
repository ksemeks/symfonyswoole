<?php

declare(strict_types=1);

namespace Lifeworks\SwooleHttpServerBundle\Http;

use Swoole\Http\Response;
use Swoole\Http\Request;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class Http
{
    public static function createSymfonyRequest(Request $swoolRequest): SymfonyRequest
    {
        $_SERVER = isset($swoolRequest->server) ? array_change_key_case($swoolRequest->server, CASE_UPPER) : [];
        if (isset($swoolRequest->header)) {
            $headers = [];
            foreach ($swoolRequest->header as $k => $v) {
                $k = str_replace('-', '_', $k);
                $headers['http_'.$k] = $v;
            }
            $_SERVER += array_change_key_case($headers, CASE_UPPER);
        }

        $_GET = isset($swoolRequest->get) ? $swoolRequest->get : [];
        $_POST = isset($swoolRequest->post) ? $swoolRequest->post : [];
        $_COOKIE = isset($swoolRequest->cookie) ? $swoolRequest->cookie : [];

        $symfonyRequest = SymfonyRequest::createFromGlobals();
        if (0 === strpos($symfonyRequest->headers->get('Content-Type'), 'application/json')) {
            $data = json_decode($swoolRequest->rawContent(), true);
            $symfonyRequest->request->replace(is_array($data) ? $data : []);
        }

        return $symfonyRequest;
    }

    public static function createSwooleResponse(Response $swooleResponse, SymfonyResponse $symfonyResponse): string
    {
        foreach ($symfonyResponse->headers->getCookies() as $cookie) {
            $swooleResponse->header('Set-Cookie', $cookie);
        }

        $symfonyResponse->headers->remove('x-powered-by');

        foreach ($symfonyResponse->headers as $name => $values) {
            $name = implode('-', array_map('ucfirst', explode('-', $name)));
            foreach ($values as $value) {
                $swooleResponse->header($name, $value);
            }
        }

        return $symfonyResponse->getContent();
    }
}
