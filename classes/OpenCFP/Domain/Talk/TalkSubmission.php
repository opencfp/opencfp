<?php

namespace OpenCFP\Domain\Talk;

class TalkSubmission 
{
    public $title;
    public $description;
    public $type;

    public static function fromNative(array $data)
    {
        $instance = new static();

        $instance->guardTitleIsAppropriateLength($data);
        $instance->guardDescriptionIsProvided($data);
        $instance->guardTalkTypeIsValid($data);
        $instance->guardLevelIsValid($data);
        $instance->guardCategoryIsValid($data);

        $instance->title = $data['title'];
        $instance->description = $data['description'];
        $instance->type = $data['type'];

        return $instance;
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

    private function guardTitleIsAppropriateLength($data)
    {
        if (!isset($data['title']) or empty($data['title'])) {
            throw new InvalidTalkSubmissionException('The title of the talk must be provided.');
        }

        if (strlen($data['title']) > 100) {
            throw new InvalidTalkSubmissionException('The title of the talk must be 100 characters or less.');
        }
    }

    private function guardDescriptionIsProvided($data)
    {
        if (!isset($data['description']) or empty($data['description'])) {
            throw new InvalidTalkSubmissionException('The description of the talk must be included.');
        }
    }

    private function guardTalkTypeIsValid($data)
    {
        if (!isset($data['type']) or empty($data['type'])) {
            throw new InvalidTalkSubmissionException('You must choose what type of talk you are submitting.');
        }

        if (!$this->isValidTalkType($data['type'])) {
            throw new InvalidTalkSubmissionException('You did not choose a valid talk type.');
        }
    }

    /**
     * Tells whether or not a talk type is supported / valid.
     *
     * This would be a good place to add user-configuration of acceptable
     * types of talks. We would inject some configuration in the factory method
     * that might drive this.
     *
     * @param $type
     *
     * @return bool true if it is valid, false otherwise.
     */
    private function isValidTalkType($type)
    {
        return in_array($type, ['regular', 'tutorial']);
    }

    private function guardLevelIsValid($data)
    {
        if (!isset($data['level']) or empty($data['level'])) {
            throw new InvalidTalkSubmissionException('You must choose when level of talk you are submitting.');
        }

        if (!$this->isValidLevel($data['level'])) {
            throw new InvalidTalkSubmissionException('You did not choose a valid talk level.');
        }
    }

    private function isValidLevel($level)
    {
        return in_array($level, ['entry', 'mid', 'advanced']);
    }

    private function guardCategoryIsValid($data)
    {
        if (!isset($data['category']) or empty($data['category'])) {
            throw new InvalidTalkSubmissionException('You must choose what category of talk you are submitting.');
        }
        
        if (!$this->isValidCategory($data['category'])) {
            throw new InvalidTalkSubmissionException('You did not choose a valid talk category.');
        }
    }

    private function isValidCategory($category)
    {
        return in_array($category, [
            'development',
            'framework',
            'database',
            'testing',
            'security',
            'devops',
            'api',
            'javascript',
            'uiux',
            'other',
            'continuousdelivery',
            'ibmi'
        ]);
    }

} 