<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2017 OpenCFP
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

    public function testGetTalkCategoriesReturnsInjectedCategories()
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

    public function testGetCategoryDisplayNameReturnsCategoryIfNotMapped()
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

    public function testGetCategoryDisplayNameReturnsCategoryDisplayNameIfMapped()
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

    public function testGetTalkTypesReturnsInjectedTypes()
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

    public function testGetTypeDisplayNameReturnsTypeIfNotMapped()
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

    public function testGetTypeDisplayNameReturnsTypeDisplayNameIfMapped()
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

    public function testGetTalkLevelsReturnsInjectedLevels()
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

    public function testGetLevelDisplayNameReturnsLevelIfNotMapped()
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

    public function testGetLevelDisplayNameReturnsLevelDisplayNameIfMapped()
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
