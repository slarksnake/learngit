<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Factory;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    protected $uid = 0;
    protected $userName = '';

    public function __construct(Request $request, Factory $factory)
    {
        if (isset($request->uid)) {
            $this->uid = $request->uid;
        }
        if (isset($request->userName)) {
            $this->userName = $request->userName;
        }


        $factory->extend(
            'eng_dash',
            function ($attribute, $value, $parameters) {
                return preg_match('/^[\w\-_ ]+$/', $value);
            },
            'This field may have 0-9a-zA-Z, as well as dashes and underscores.'
        );
    }

    protected function buildFailedValidationResponse(Request $request, array $errors)
    {
        $all_errors = '';
        foreach ($errors as $e) {
            $all_errors .= $e[0];
            break;
        }

        return response()->json(['msg' => $all_errors, 'code' => 9001], 422);
    }


    protected function logException(Exception $e)
    {
        $msg = $e->getMessage();
        $code = $e->getCode();

        // 偶数错误为非用户错误
        if ($code % 2 == 0) {
            // TODO Log unordinary error into DB
        }

        if (strpos($msg, 'SQL') !== false) {
            // TODO SQL error
            $code = 0;
        }

        $httpStatusCode = 400;
        if (strpos($msg, '@') !== false) {
            list($msg, $httpStatusCode) = explode('@', $msg);
        }

        return response()->json(['msg'=>$msg, 'code'=>$code], $httpStatusCode);
    }
}
