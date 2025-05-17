<?php 

namespace App\Util;
use Illuminate\Http\Response;

trait ApiResponse
{

    public function successResponse($data,$code = Response::HTTP_OK)
    {
          return response()->json(["data"=>$data],$code);
    }

    public function errorResponse($message,$code)
    {
          return response()->json(["error"=>$message,"code"=>$code],$code);
    }

    public function responseJson($data,$code = Response::HTTP_OK)
    {
          return response()->json($data,$code)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

}