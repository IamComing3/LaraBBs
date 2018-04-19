<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\VerificationCodeRequest;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class VerificationCodesController extends Controller
{
    public function store(VerificationCodeRequest $request)
    {
        $phone = $request->phone;

        if (!app()->environment('production')) {
            $code = '1234';
        } else {
            $code = str_pad(random_int(1, 9999), 4, 0, STR_PAD_LEFT);

            try {
                Log::info($code);
            } catch (ClientException $exception) {
                $response = $exception->getResponse();
                $result = json_decode($response->getBody()->getContents(), true);
                return $this->response->errorInternal($result['msg'] ?? '验证码发送异常');
            }
        }

        $key = 'verificationCode_' . str_random(15);
        $expireAt = now()->addMinute(10);
        // 缓存验证码，10 分钟过期
        Cache::put($key, ['phone' => $phone, 'code' => $code], $expireAt);

        return $this->response->array([
            'key' => $key,
            'expireAt' => $expireAt->toDateTimeString()
        ])->setStatusCode(201);
    }
}
