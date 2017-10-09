<?php


namespace OpenCFP\Domain\Model;

use Illuminate\Database\Eloquent\Model;

class TalkComment extends Model
{
    protected $table = 'talk_comments';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function talk()
    {
        return $this->belongsTo(Talk::class);
    }
}
