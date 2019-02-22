<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2019 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Test\Unit\Http\View;

use Localheinz\Test\Util\Helper;
use OpenCFP\Http\View\TalkHelper;
use PHPUnit\Framework;

final class TalkHelperTest extends Framework\TestCase
{
    use Helper;

    /**
     * @test
     */
    public function getTalkCategoriesReturnsDefaultCategoriesIfNoneHaveBeenInjected()
    {
        $helper = new TalkHelper(
            null,
            null,
            null
        );

        $defaultCategories = [
            'api'                => 'APIs (REST, SOAP, etc.)',
            'continuousdelivery' => 'Continuous Delivery',
            'database'           => 'Database',
            'development'        => 'Development',
            'devops'             => 'Devops',
            'framework'          => 'Framework',
            'ibmi'               => 'IBMi',
            'javascript'         => 'JavaScript',
            'personal'           => 'Personal Skills',
            'security'           => 'Security',
            'testing'            => 'Testing',
            'uiux'               => 'UI/UX',
            'other'              => 'Other',
        ];

        $this->assertSame($defaultCategories, $helper->getTalkCategories());
    }

    /**
     * @test
     */
    public function getTalkCategoriesReturnsInjectedCategories()
    {
        $faker = $this->faker();

        $categories = \array_combine(
            $faker->words(5),
            $faker->sentences(5)
        );

        $helper = new TalkHelper(
            $categories,
            null,
            null
        );

        $this->assertSame($categories, $helper->getTalkCategories());
    }

    /**
     * @test
     */
    public function getCategoryDisplayNameReturnsCategoryIfNotMapped()
    {
        $faker = $this->faker();

        $category = $faker->unique()->word;

        $helper = new TalkHelper(
            null,
            null,
            null
        );

        $this->assertSame($category, $helper->getCategoryDisplayName($category));
    }

    /**
     * @test
     */
    public function getCategoryDisplayNameReturnsCategoryDisplayNameIfMapped()
    {
        $faker = $this->faker();

        $category            = $faker->word;
        $categoryDisplayName = $faker->sentence;

        $categories = [
            $category => $categoryDisplayName,
        ];

        $helper = new TalkHelper(
            $categories,
            null,
            null
        );

        $this->assertSame($categoryDisplayName, $helper->getCategoryDisplayName($category));
    }

    /**
     * @test
     */
    public function getTalkTypesReturnsDefaultTypesIfNoneHaveBeenInjected()
    {
        $helper = new TalkHelper(
            null,
            null,
            null
        );

        $defaultTypes = [
            'regular'  => 'Regular',
            'tutorial' => 'Tutorial',
        ];

        $this->assertSame($defaultTypes, $helper->getTalkTypes());
    }

    /**
     * @test
     */
    public function getTalkTypesReturnsInjectedTypes()
    {
        $faker = $this->faker();

        $types = \array_combine(
            $faker->words(5),
            $faker->sentences(5)
        );

        $helper = new TalkHelper(
            null,
            null,
            $types
        );

        $this->assertSame($types, $helper->getTalkTypes());
    }

    /**
     * @test
     */
    public function getTypeDisplayNameReturnsTypeIfNotMapped()
    {
        $faker = $this->faker();

        $type = $faker->unique()->word;

        $helper = new TalkHelper(
            null,
            null,
            null
        );

        $this->assertSame($type, $helper->getTypeDisplayName($type));
    }

    /**
     * @test
     */
    public function getTypeDisplayNameReturnsTypeDisplayNameIfMapped()
    {
        $faker = $this->faker();

        $type            = $faker->word;
        $typeDisplayName = $faker->sentence;

        $types = [
            $type => $typeDisplayName,
        ];

        $helper = new TalkHelper(
            null,
            null,
            $types
        );

        $this->assertSame($typeDisplayName, $helper->getTypeDisplayName($type));
    }

    /**
     * @test
     */
    public function getTalkLevelsReturnsDefaultTypesIfNoneHaveBeenInjected()
    {
        $helper = new TalkHelper(
            null,
            null,
            null
        );

        $defaultLevels = [
            'entry'    => 'Entry level',
            'mid'      => 'Mid-level',
            'advanced' => 'Advanced',
        ];

        $this->assertSame($defaultLevels, $helper->getTalkLevels());
    }

    /**
     * @test
     */
    public function getTalkLevelsReturnsInjectedLevels()
    {
        $faker = $this->faker();

        $levels = \array_combine(
            $faker->words(5),
            $faker->sentences(5)
        );

        $helper = new TalkHelper(
            null,
            $levels,
            null
        );

        $this->assertSame($levels, $helper->getTalkLevels());
    }

    /**
     * @test
     */
    public function getLevelDisplayNameReturnsLevelIfNotMapped()
    {
        $faker = $this->faker();

        $level = $faker->unique()->word;

        $helper = new TalkHelper(
            null,
            null,
            null
        );

        $this->assertSame($level, $helper->getLevelDisplayName($level));
    }

    /**
     * @test
     */
    public function getLevelDisplayNameReturnsLevelDisplayNameIfMapped()
    {
        $faker = $this->faker();

        $level            = $faker->word;
        $levelDisplayName = $faker->sentence;

        $levels = [
            $level => $levelDisplayName,
        ];

        $helper = new TalkHelper(
            null,
            $levels,
            null
        );

        $this->assertSame($levelDisplayName, $helper->getLevelDisplayName($level));
    }
}
