<?php


namespace App\Traits;


trait JsonResponse
{

    public static function success($data, $message = null, $code = null)
    {
        $responseData = [
            "message" => $message ?? "Successful",
            "data" => !is_array($data) ? $data->toArray() : $data,
            "code" => $code ?? 200
        ];

        return response()->json($responseData, $code ?? 200);
    }

}
