<?php

namespace RusBios\MediaHub\Controllers;

use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;

trait ResponseTrait
{
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
