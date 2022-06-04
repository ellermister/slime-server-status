<?php

/**
 * 返回JSON
 *
 * @param $message
 * @param $code
 * @param $data
 * @return array
 */
function rjson(string $message, int $code, array $data = null): array
{
    return [
        'message' => $message,
        'code'    => $code,
        'data'    => $data
    ];
}