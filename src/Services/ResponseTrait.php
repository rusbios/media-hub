<?php

namespace MediaHub\Services;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\{Request, Response};
use Illuminate\Pagination\LengthAwarePaginator;
use MediaHub\Models\UserModels;

trait ResponseTrait
{
    /**
     * @param Request $request
     * @return UserModels
     * @throws AuthenticationException
     */
    protected function decodeToken(Request $request): UserModels
    {
        $token = $request->header('X-API-TOKEN', $request->get('api_token'));

        if (!$token) {
            throw new AuthenticationException();
        }

        if (TokenService::isValid($request, $token)) {
            return TokenService::getUser($token);
        }

        throw new AuthenticationException('Invalid token');
    }

    /**
     * @param array $data
     * @return Response
     */
    protected function getSuccess(array $data = []): Response
    {
        return new Response(['success' => true] + $data);
    }

    /**
     * @param string $message
     * @param int $code
     * @param Request|null $request
     * @return Response
     */
    protected function getError(string $message, int $code = Response::HTTP_BAD_REQUEST, Request $request = null): Response
    {
        if ($request && config('app.debug')) {
            $context = $request->all();
            $context['response_code'] = $code;
            $context['request_url'] = $request->url();
            Log::info($message, $context);
        }

        return new Response([
            'success' => false,
            'error' => $message,
        ], $code);
    }

    /**
     * @param LengthAwarePaginator $paginator
     * @param array $data
     * @return Response
     */
    protected function getPagination(LengthAwarePaginator $paginator, array $data = []): Response
    {
        return new Response([
            'success' => true,
            'items' => $paginator->items(),
            'pagination' => [
                'total' => $paginator->total(),
                'page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
            ],
        ] + $data);
    }
}
