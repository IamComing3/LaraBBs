<?php

namespace App\Models\Traits;

use Cache;
use DB;
use App\Models\Topic;
use App\Models\Reply;
use Carbon\Carbon;

trait ActiveUserHelper
{
    // 用于存放临时用户数据
    protected $users = [];

    // 配置信息
    protected $topic_weight = 4; // 话题权重
    protected $reply_weight = 1; // 回复权重
    protected $pass_days = 7;    // 多少天内发表过内容
    protected $user_member = 6;  // 取出多少用户

    // 缓存相关配置
    protected $cache_key = 'larabbs_active_users';
    protected $cache_expire_in_minutes = 65;

    public function getActiveUsers()
    {
        // 尝试从缓存中取出 cache_key 对应的数据，如果能取到，直接返回数据
        // 否则运行匿名函数中的代码来取出活跃用户数据，返回的同时作缓存
        return Cache::remember($this->cache_key, $this->cache_expire_in_minutes, function() {
            return $this->calculateActiveUsers();
        });
    }

    public function calculateActiveUsers()
    {
        $this->calculateTopicScore();
        $this->calculateReplyScore();

        // 数据按照得分排序
        $users = array_sort($this->users, function ($user) {
            return $user['score'];
        });

        // 分数由高到低倒序，第二个参数为保持数据的 KEY 不变
        $users = array_reverse($users, true);

        // 只获取想要的数量
        $users = array_slice($users, 0, $this->user_member, true);

        // 新建一个空集合
        $active_users = collect();

        foreach ($users as $user_id => $user) {
            // 找寻用户是否存在
            $user = $this->find($user_id);

            // 如果用户存在
            if (count($user)) {
                // 将此用户实体放入集合末尾
                $active_users->push($user);
            }
        }

        return $active_users;
    }

    private function calculateTopicScore()
    {
        // 从话题数据表里取出限定时间范围内，有发表过话题的用户
        // 并且同事取出用户此段时间内发布话题的数量
        $topic_users = Topic::query()->select(DB::raw('user_id, count(*) as topic_count'))
                                     ->where('created_at', '>=', Carbon::now()->subDays($this->pass_days))
                                     ->groupBy('user_id')
                                     ->get();
        // 根据话题数量计算得分
        foreach ($topic_users as $value) {
            $this->users[$value->user_id]['score'] = $value->topic_count * $this->topic_weight;
        }
    }

    private function calculateReplyScore()
    {
        // 从回复数据表里取出限定时间范围内，有发表过回复的用户
        // 并且同事取出用户此段时间内发布回复的数量
        $reply_users = Reply::query()->select(DB::raw('user_id, count(*) as reply_count'))
                                     ->where('created_at', '>=', Carbon::now()->subDays($this->pass_days))
                                     ->groupBy('user_id')
                                     ->get();
        // 根据回复数量计算得分
        foreach ($reply_users as $value) {
            $reply_score = $value->reply_count * $this->reply_weight;

            if (isset($this->users[$value->user_id])) {
                $this->users[$value->user_id]['score'] += $reply_score;
            } else {
                $this->users[$value->user_id]['score'] = $reply_score;
            }
        }
    }

    public function calculateAndCacheActiveUsers()
    {
        // 取得活跃用户列表
        $active_users = $this->calculateActiveUsers();

        // 缓存
        $this->cacheActiveUsers($active_users);
    }

    private function cacheActiveUsers($active_users)
    {
        // 将数据放入缓存
        Cache::put($this->cache_key, $active_users, $this->cache_expire_in_minutes);
    }
}
