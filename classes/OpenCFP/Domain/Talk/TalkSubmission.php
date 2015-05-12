<?php

namespace OpenCFP\Domain\Talk;

class TalkSubmission 
{
    public static function fromNative(array $data)
    {
        $this->

        return new static();
    }

    public function toTalk()
    {
        return new Talk([
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'level' => $this->level,
            'category' => $this->category,
            'desired' => $this->desired,
            'slides' => $this->slides,
            'other' => $this->other,
            'sponsor' => $this->sponsor
        ]);
    }
} 