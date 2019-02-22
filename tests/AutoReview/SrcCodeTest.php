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

namespace OpenCFP\Test\AutoReview;

use Localheinz\Classy;
use Localheinz\Test\Util\Helper;
use PHPUnit\Framework;
use Symfony\Component\HttpFoundation;

final class SrcCodeTest extends Framework\TestCase
{
    use Helper;

    /**
     * @test
     */
    public function controllerActionsUseResponseReturnType(): void
    {
        $actionsWithoutReturnTypes = $this->methodNames(\array_filter($this->controllerActions(), function (\ReflectionMethod $method) {
            $returnType = (string) $method->getReturnType();

            return $returnType !== HttpFoundation\Response::class;
        }));

        $this->assertEmpty($actionsWithoutReturnTypes, \sprintf(
            "Failed asserting that the controller actions\n\n%s\n\ndeclare \"%s\" as return type.",
            ' - ' . \implode("\n - ", $actionsWithoutReturnTypes),
            HttpFoundation\Response::class
        ));
    }

    /**
     * @test
     */
    public function controllerActionsUseActionSuffix(): void
    {
        $actionsWithoutSuffix = $this->methodNames(\array_filter($this->controllerActions(), function (\ReflectionMethod $method) {
            return \preg_match('/Action$/', $method->getName()) === 0;
        }));

        $this->assertEmpty($actionsWithoutSuffix, \sprintf(
            "Failed asserting that the controller actions\n\n%s\n\nuse  \"Action\" as suffix.",
            ' - ' . \implode("\n - ", $actionsWithoutSuffix)
        ));
    }

    /**
     * @return \ReflectionMethod[]
     */
    private function controllerActions(): array
    {
        $constructs = Classy\Constructs::fromDirectory(__DIR__ . '/../../src/Http/Controller');

        $actions = [];

        foreach ($constructs as $construct) {
            $reflection = new \ReflectionClass($construct->name());

            if (!$reflection->isInstantiable()) {
                continue;
            }

            foreach ($reflection->getMethods() as $method) {
                if ($method->isAbstract() || $method->isConstructor() || !$method->isPublic() || $method->isStatic()) {
                    continue;
                }

                $actions[] = $method;
            }
        }

        return $actions;
    }

    /**
     * @param \ReflectionMethod[] $methods
     *
     * @return string[]
     */
    private function methodNames(array $methods): array
    {
        $methodNames = \array_map(function (\ReflectionMethod $method) {
            return \sprintf(
                '%s::%s',
                $method->getDeclaringClass()->getName(),
                $method->getName()
            );
        }, $methods);

        \sort($methodNames);

        return $methodNames;
    }

    /**
     * @dataProvider providerProductionClassesAreAbstractOrFinal
     *
     * @param string $directory
     *
     * @test
     */
    public function productionClassesAreAbstractOrFinal(string $directory): void
    {
        $this->assertClassesAreAbstractOrFinal($directory);
    }

    public function providerProductionClassesAreAbstractOrFinal(): array
    {
        $directories = [
            'http-actions' => __DIR__ . '/../../src/Http/Action',
        ];

        return \array_map(function (string $directory) {
            return [
              $directory,
            ];
        }, $directories);
    }
}
