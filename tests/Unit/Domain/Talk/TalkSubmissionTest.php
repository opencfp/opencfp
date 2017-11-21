<?php

namespace OpenCFP\Test\Unit\Domain\Talk;

use OpenCFP\Domain\Talk\TalkSubmission;

class TalkSubmissionTest extends \PHPUnit\Framework\TestCase
{
    /** @test */
    public function it_should_be_created_from_native_format()
    {
        // An associative array would be considered "native" here.
        // There is an assumption that any inputs have been cleaned previously. This class
        // represents the TalkSubmission only.

        $submission = TalkSubmission::fromNative([
            'title'       => 'Happy Path Submission',
            'description' => 'I play by the rules.',
            'type'        => 'regular',
            'level'       => 'entry',
            'category'    => 'api',
        ]);

        // Factory method for talk out of submission.
        // Responsible for creating data-mapper Talk entity from cleaned inputs.
        $talk = $submission->toTalk();

        $this->assertEquals('Happy Path Submission', $talk->title);
        $this->assertEquals('I play by the rules.', $talk->description);
        $this->assertEquals('regular', $talk->type);
        $this->assertEquals('entry', $talk->level);
        $this->assertEquals('api', $talk->category);
        $this->assertEmpty($talk->slides);
    }

    /**
     * @test
     * @dataProvider invalidTalkTitles
     */
    public function it_guards_that_title_is_appropriate_length($title)
    {
        $this->expectException(\OpenCFP\Domain\Talk\InvalidTalkSubmissionException::class);
        $this->expectExceptionMessage('title');

        TalkSubmission::fromNative(['title' => $title]);
    }

    public function invalidTalkTitles()
    {
        return [
            [''],
            ['String over one-hundred characters long: Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc vel placerat nulla. Nunc orci aliquam.'],
        ];
    }

    /**
     * @test
     */
    public function it_guards_that_description_is_provided()
    {
        $this->expectException(\OpenCFP\Domain\Talk\InvalidTalkSubmissionException::class);
        $this->expectExceptionMessage('description');

        TalkSubmission::fromNative([
            'title'       => 'Talk With No Description',
            'description' => '',
        ]);
    }

    /**
     * @test
     */
    public function it_guards_that_invalid_talk_types_cannot_be_used()
    {
        $this->expectException(\OpenCFP\Domain\Talk\InvalidTalkSubmissionException::class);
        $this->expectExceptionMessage('talk type');

        TalkSubmission::fromNative([
            'title'       => 'Some off-the-wall Talk Type',
            'description' => 'I do not play by the rules.',
            'type'        => 'hamburger',
        ]);
    }

    /**
     * @test
     */
    public function it_guards_that_invalid_level_cannot_be_used()
    {
        $this->expectException(\OpenCFP\Domain\Talk\InvalidTalkSubmissionException::class);
        $this->expectExceptionMessage('level');

        TalkSubmission::fromNative([
            'title'       => 'Invalid Skill Level Talk',
            'description' => 'I do not play by the rules.',
            'type'        => 'regular',
            'level'       => 'over 9000',
        ]);
    }

    /**
     * @test
     */
    public function it_guards_that_invalid_categories_cannot_be_assigned()
    {
        $this->expectException(\OpenCFP\Domain\Talk\InvalidTalkSubmissionException::class);
        $this->expectExceptionMessage('category');

        TalkSubmission::fromNative([
            'title'       => 'Invalid Categorized Talk',
            'description' => 'I do not play by the rules.',
            'type'        => 'regular',
            'level'       => 'entry',
            'category'    => 'skylanders',
        ]);
    }
}
