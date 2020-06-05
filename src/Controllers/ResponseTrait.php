<?php

namespace RusBios\MediaHub\Controllers;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use RusBios\MediaHub\Models\User;
use RusBios\MediaHub\Services\Token;

trait ResponseTrait
{
    /**
     * @param Request $request
     * @return User
     * @throws AuthenticationException
     */
    protected function decodeToken(Request $request): User
    {
        $token = $request->header('X-API-TOKEN', $request->get('api_token'));

        if (!$token) {
            throw new AuthenticationException();
        }

        if (Token::isValid($request, $token)) {
            return Token::getUser($token);
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
     * @return Response
     */
    protected function getError(string $message, int $code = Response::HTTP_BAD_REQUEST): Response
    {
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
