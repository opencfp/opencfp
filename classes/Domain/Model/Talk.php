<?php

namespace OpenCFP\Domain\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Talk extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function speaker()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'talk_id');
    }

    public function comments()
    {
        return $this->hasMany(TalkComment::class);
    }

    public function meta()
    {
        return $this->hasMany(TalkMeta::class, 'talk_id');
    }

    /**
     * Return a collection of recent talks
     *
     * @param Builder $query
     * @param         $admin_id
     * @param int     $limit
     *
     * @return array|Talk[]
     */
    public function scopeRecent(Builder $query, $admin_id, $limit = 10)
    {
        return $query
            ->orderBy('created_at')
            ->with(['favorites', 'meta'])
            ->take($limit)
            ->get()
            ->map(function($talk) use ($admin_id) {
                return $this->createdFormattedOutput($talk, $admin_id);
            });
    }
    /**
     * Iterates over DBAL objects and returns a formatted result set
     *
     * @param  mixed   $talk
     * @param  integer $admin_user_id
     * @return array
     */
    public function createdFormattedOutput($talk, $admin_user_id, $userData = true)
    {
        if ($talk->favorites) {
            foreach ($talk->favorites as $favorite) {
                if ($favorite->admin_user_id == $admin_user_id) {
                    $talk->favorite = 1;
                }
            }
        }
        $meta = $talk->meta->where('admin_user_id', $admin_user_id)->first();

        $output = [
            'id' => $talk->id,
            'title' => $talk->title,
            'type' => $talk->type,
            'category' => $talk->category,
            'created_at' => $talk->created_at,
            'selected' => $talk->selected,
            'favorite' => $talk->favorite,
            'meta' => $meta ?: ['rating' => 0, 'viewed' => 0],
            'description' => $talk->description,
            'slides' => $talk->slides,
            'other' => $talk->other,
            'level' => $talk->level,
            'desired' => $talk->desired,
            'sponsor' => $talk->sponsor,
        ];
        if ($talk->speaker && $userData) {
            $output['user'] = [
                'id' => $talk->speaker->id,
                'first_name' => $talk->speaker->first_name,
                'last_name' => $talk->speaker->last_name,
            ];
            $output += [
                'speaker_id' => $talk->speaker->id,
                'speaker_first_name' => $talk->speaker->first_name,
                'speaker_last_name' => $talk->speaker->last_name,
                'speaker_email' => $talk->speaker->email,
                'speaker_company' => $talk->speaker->company,
                'speaker_twitter' => $talk->speaker->twitter,
                'speaker_airport' => $talk->speaker->airport,
                'speaker_hotel' => $talk->speaker->hotel,
                'speaker_transportation' => $talk->speaker->transportation,
                'speaker_info' => $talk->speaker->info,
                'speaker_bio' => $talk->speaker->bio,
            ];
        }
        if ($talk->total_rating) {
            $output['total_rating'] = $talk->total_rating;
            $output['review_count'] = $talk->review_count;
        }
        return $output;
    }
}
