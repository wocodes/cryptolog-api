<?php


namespace App\Traits;


trait JsonResponse
{

    public static function success($data, $message = null, $code = null)
    {
        $responseData = [
            "message" => $message ?? "Successful",
            "data" => is_object($data) ? $data->toArray() : $data,
            "code" => $code ?? 200
        ];

        return response()->json($responseData, $code ?? 200);
    }

    public static function error($data = [], $message = null, $code = null)
    {
        $responseData = [
            "message" => $message ?? "Ooops! Something wrong happened",
            "data" => is_object($data) ? $data->toArray() : $data,
            "code" => $code ?? 400
        ];

        return response()->json($responseData, $code ?? 400);
    }

}
