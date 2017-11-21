<?php

namespace OpenCFP\Test\Http\Form;

use OpenCFP\Test\Helper\Faker\GeneratorTrait;

class TalkFormTest extends \PHPUnit\Framework\TestCase
{
    use GeneratorTrait;

    private $purifier;

    protected function setUp()
    {
        $this->purifier = new \HTMLPurifier();
    }

    /**
     * Test that form object correctly detects if all the required fields
     * are in the user-submitted data
     *
     * @test
     * @dataProvider hasRequiredProvider
     *
     * @param array $rawData  serialized user-submitted data
     * @param bool  $response
     */
    public function correctlyDetectsRequiredFields($rawData, $response)
    {
        $data = unserialize($rawData);
        $form = new \OpenCFP\Http\Form\TalkForm($data, $this->purifier);

        $this->assertEquals(
            $response,
            $form->hasRequiredFields(),
            sprintf(
                '%s::hasRequired() did not work correctly',
                \OpenCFP\Http\Form\TalkForm::class
            )
        );
    }

    /**
     * Data provider correctlyDetectsRequiredFields
     *
     * @return array
     */
    public function hasRequiredProvider()
    {
        $badData = [
            'title'       => 'Bad Data',
            'description' => 'Hey, why are we missing fields!',
        ];
        $goodData = [
            'title'       => 'Talk Title',
            'description' => 'Description of our talk',
            'type'        => 'session',
            'category'    => 'development',
            'level'       => 'entry',
            'slides'      => 'http://slideshare.net',
            'other'       => 'Misc comments',
            'desired'     => 1,
            'sponsor'     => 1,
            'user_id'     => 1,
        ];
        $extendedData          = $goodData;
        $extendedData['extra'] = 'Extra data in $_POST but we ignore it';

        return [
            [serialize($badData), false],
            [serialize($goodData), true],
            [serialize($extendedData), true],
        ];
    }

    /**
     * Test that form object correctly detects if all the required fields
     * are in the user-submitted data
     *
     * @test
     * @dataProvider hasNoDesiredOrSponsorProvider
     *
     * @param array $rawData  serialized user-submitted data
     * @param bool  $response
     */
    public function submitsTalkWhenNoDesiredOrSponrosIncluded($rawData, $response)
    {
        $data = unserialize($rawData);
        $form = new \OpenCFP\Http\Form\TalkForm($data, $this->purifier);
        $this->assertEquals(
            $response,
            $form->hasRequiredFields(),
            sprintf(
                '%s::hasRequired() did not work correctly',
                \OpenCFP\Http\Form\TalkForm::class
            )
        );
    }

    /**
     * Data provider correctlyDetectsRequiredFields
     *
     * @return array
     */
    public function hasNoDesiredOrSponsorProvider()
    {
        $goodData = [
            'title'       => 'Talk Title',
            'description' => 'Description of our talk',
            'type'        => 'session',
            'category'    => 'development',
            'level'       => 'entry',
            'slides'      => 'http://slideshare.net',
            'other'       => 'Misc comments',
            'user_id'     => 1,
        ];

        return [
            [serialize($goodData), true],
        ];
    }

    /**
     * Test that title data is properly validated
     *
     * @test
     * @dataProvider titleValidatesProvider
     *
     * @param string $title
     * @param bool   $expectedResponse
     */
    public function titleValidatesCorrectly($title, $expectedResponse)
    {
        $data = ['title' => $title];
        $form = new \OpenCFP\Http\Form\TalkForm($data, $this->purifier);
        $form->sanitize();

        $this->assertEquals(
            $expectedResponse,
            $form->validateTitle(),
            sprintf(
                '%s::validateTitle() did not apply validation rules correctly',
                \OpenCFP\Http\Form\TalkForm::class
            )
        );
    }

    /**
     * Data provider for titleValidatesCorrectly
     *
     * @return array
     */
    public function titleValidatesProvider()
    {
        $faker = $this->getFaker();

        return [
            [substr($faker->text(90), 0, 90), true],
            [null, false],
            ['This is a string that could be more than 100 characters long but will we really know for sure until I check it out?', false],
            ['A little bit of this & that', true],
        ];
    }

    /**
     * Test that description data is being properly validated
     *
     * @test
     * @dataProvider descriptionValidatesProvider
     *
     * @param string $description
     * @param bool   $expectedResponse
     */
    public function descriptionValidatesCorrectly($description, $expectedResponse)
    {
        $data = ['description' => $description];
        $form = new \OpenCFP\Http\Form\TalkForm($data, $this->purifier);
        $form->sanitize();

        $this->assertEquals(
            $expectedResponse,
            $form->validateDescription(),
            sprintf(
                '%s::validateDescription() did not apply validation rules correctly',
                \OpenCFP\Http\Form\TalkForm::class
            )
        );
    }

    /**
     * Data provider for descriptionValidatesCorrectly
     *
     * @return array
     */
    public function descriptionValidatesProvider()
    {
        $faker = $this->getFaker();

        return [
            [$faker->text(), true],
            [null, false],
            ['<script>alert("XSS");</script>', false],
        ];
    }

    /**
     * Test that validates the talk type
     *
     * @test
     * @dataProvider typeProvider
     *
     * @param string $type
     * @param bool   $expectedResponse
     */
    public function typeValidatesCorrectly($type, $expectedResponse)
    {
        $data = ['type' => $type];
        $form = new \OpenCFP\Http\Form\TalkForm(
            $data,
            $this->purifier,
            ['types' => ['regular' => 'Regular', 'tutorial' => 'Tutorial']]
        );
        $form->sanitize();

        $this->assertEquals(
            $expectedResponse,
            $form->validateType(),
            sprintf(
                '%s::validateType() did not apply validation rules correctly',
                \OpenCFP\Http\Form\TalkForm::class
            )
        );
    }

    /**
     * Data provider for typeValidatesCorrectly
     *
     * @return bool
     */
    public function typeProvider()
    {
        return [
            ['regular', true],
            ['tutorial', true],
            ['foo', false],
            [null, false],
            [false, false],
            [1, false],
            [true, false],
        ];
    }

    /**
     * Test that validates the talk category
     *
     * @test
     * @dataProvider categoryProvider
     *
     * @param string $category
     * @param bool   $expectedResponse
     */
    public function categoryValidatesCorrectly($category, $expectedResponse)
    {
        $data = ['category' => $category];
        $form = new \OpenCFP\Http\Form\TalkForm($data, $this->purifier, ['categories' => ['test1' => 'Test 1', 'test2' => 'Test 2']]);
        $form->sanitize();

        $this->assertEquals(
            $expectedResponse,
            $form->validateCategory(),
            sprintf(
                '%s::validateType() did not apply validation rules correctly',
                \OpenCFP\Http\Form\TalkForm::class
            )
        );
    }

    /**
     * Data provider for typeValidatesCorrectly
     *
     * @return bool
     */
    public function categoryProvider()
    {
        return [
            ['test1', true],
            ['test2', true],
            ['foo', false],
            [null, false],
            [false, false],
            [1, false],
            [true, false],
        ];
    }

    /**
     * Data provider for speakerIdValidates
     *
     * @return array
     */
    public function speakerIdProvider()
    {
        $validSpeakerInfo = [
            'user_id' => 1,
            'info'    => 'Special speaker info',
        ];

        return [
            [1, $validSpeakerInfo, true],
            [0, false, false],
            [null, false, false],
            [true, false, false],
            [false, false, false],
            ['user', false, false],
        ];
    }

    /**
     * Test that validates the talk type
     *
     * @test
     * @dataProvider levelProvider
     *
     * @param string $level
     * @param bool   $expectedResponse
     */
    public function levelValidatesCorrectly($level, $expectedResponse)
    {
        $data = ['level' => $level];
        $form = new \OpenCFP\Http\Form\TalkForm(
            $data,
            $this->purifier,
            ['levels' => ['entry' => 'Entry', 'advanced' => 'Advanced']]
        );
        $form->sanitize();
        $this->assertEquals(
            $expectedResponse,
            $form->validateLevel(),
            sprintf(
                '%s::validateType() did not apply validation rules correctly',
                \OpenCFP\Http\Form\TalkForm::class
            )
        );
    }

    /**
     * Data provider for levelValidatesCorrectly
     *
     * @return array
     */
    public function levelProvider()
    {
        return [
            ['advanced', true],
            ['entry', true],
            ['foo', false],
            [null, false],
            [false, false],
            [1, false],
            [true, false],
        ];
    }
}
