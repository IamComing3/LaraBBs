<?php

namespace App\Observers;

use App\Models\Reply;
use App\Notifications\TopicReplied;

// creating, created, updating, updated, saving,
// saved,  deleting, deleted, restoring, restored

class ReplyObserver
{
    public function creating(Reply $reply)
    {
         $reply->content = clean($reply->content, 'user_topic_body');
    }

    public function created(Reply $reply)
    {
        $topic = $reply->topic;

        // 回复量自增
        $topic->increment('reply_count', 1);

        // 如果回复的作者不是话题的作者，发出通知
        if (! $reply->user->isAuthorOf($topic)) {
            $topic->user->notify(new TopicReplied($reply));
        }
    }
}
