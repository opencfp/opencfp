<?php

namespace OpenCFP\Domain\Model;

use Illuminate\Database\Eloquent\Model;

class TalkMeta extends Model
{
    protected $table = 'talk_meta';
    public $timestamps = false;
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function talk()
    {
        return $this->belongsTo(Talk::class, 'talk_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }
}
