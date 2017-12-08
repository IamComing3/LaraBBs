<?php

namespace App\Http\Requests;

class ReplyRequest extends Request
{
    public function rules()
    {
        switch($this->method())
        {
            // CREATE
            case 'POST':
            case 'DELETE':
            default:
            {
                return [
                    'content' => 'required|min:2',
                    'topic_id' => 'required'
                ];
            };
        }
    }

    public function messages()
    {
        return [
            'content.required' => '回复内容不能为空哦~',
            'content.min' => '回复内容至少两个字符哦~',
            'topic_id'  => '服务器错误'
        ];
    }
}
