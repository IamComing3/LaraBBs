<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\UserRequest;
use App\Models\User;
use App\Transformer\UserTransformer;
use Illuminate\Support\Facades\Cache;

class UsersController extends Controller
{
    /**
     * User register by verification and phone
     */
    public function store(UserRequest $request)
    {
        $verifyData = Cache::get($request->verification_key);

        if (! $verifyData) {
            return $this->response->error('验证码已失效', 422);
        }

        if (! hash_equals($verifyData['code'], $request->verification_code)) {
            // 返回 401
            return $this->response->errorUnauthorized('验证码错误');
        }

        $user = User::create([
            'name' => $request->name,
            'phone' => $verifyData['phone'],
            'password' => bcrypt($request->password)
        ]);

        Cache::forget($request->verification_key);

        // 返回 201
        return $this->response->item($user, new UserTransformer())
            ->setMeta([
                'access_token' => \Auth::guard('api')->fromUser($user),
                'token_type' => 'Bearer',
                'expires_in' => \Auth::guard('api')->factory()->getTTL() * 60
            ])
            ->setStatusCode(201);
    }

    public function me()
    {
        return $this->response->item($this->user, new UserTransformer());
    }
}
