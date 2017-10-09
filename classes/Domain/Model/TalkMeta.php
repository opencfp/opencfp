<?php

namespace OpenCFP\Domain\Model;

use Illuminate\Database\Eloquent\Model;

class TalkMeta extends Model
{
    protected $table = 'talk_meta';
    public $timestamps = false;

    public function talk()
    {
        return $this->belongsTo(Talk::class, 'talk_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }
}
