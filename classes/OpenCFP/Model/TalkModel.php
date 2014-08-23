<?php
namespace OpenCFP\Model;

class TalkModel extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'talks';

    public function findById($id)
    {
        return self::find($id);
    }

    public function save()
    {
        return parent::save();
    }
}
