<?php


namespace App\Traits;


trait JsonResponse
{

    public static function success(array $data, $message = null, $code = null)
    {
        $responseData = [
            "message" => $message ?? "Successful",
            "data" => $data,
            "code" => $code ?? 200
        ];

        return response()->json($responseData, $code ?? 200);
    }

}
