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

namespace OpenCFP\Provider;

use Cartalyst\Sentinel\Sentinel;
use Illuminate\Database\Capsule\Manager as Capsule;
use OpenCFP\Application;
use OpenCFP\Application\Speakers;
use OpenCFP\Domain\Model\Airport;
use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Services\AccountManagement;
use OpenCFP\Domain\Services\AirportInformationDatabase;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\IdentityProvider;
use OpenCFP\Domain\Speaker\SpeakerRepository;
use OpenCFP\Infrastructure\Auth\CsrfValidator;
use OpenCFP\Infrastructure\Auth\SentinelAccountManagement;
use OpenCFP\Infrastructure\Auth\SentinelAuthentication;
use OpenCFP\Infrastructure\Auth\SentinelIdentityProvider;
use OpenCFP\Infrastructure\Crypto\PseudoRandomStringGenerator;
use OpenCFP\Infrastructure\Persistence\IlluminateSpeakerRepository;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

final class ApplicationServiceProvider implements ServiceProviderInterface
{
    /**s
     * {@inheritdoc}
     */
    public function register(Container $app)
    {
        $app[AccountManagement::class] = function ($app) {
            return new SentinelAccountManagement($app[Sentinel::class]);
        };

        $app[IdentityProvider::class] = function ($app) {
            return new SentinelIdentityProvider($app[Sentinel::class], $app[SpeakerRepository::class]);
        };

        $app[Authentication::class] = function ($app) {
            return new SentinelAuthentication($app[Sentinel::class], $app[AccountManagement::class]);
        };

        $app[CsrfValidator::class] = function (Application $app) {
            return new CsrfValidator($app['csrf.token_manager']);
        };

        $app[SpeakerRepository::class] = function () {
            return new IlluminateSpeakerRepository(new User());
        };

        $app[Capsule::class] = function ($app) {
            $capsule = new Capsule();

            $capsule->addConnection([
                'driver'    => 'mysql',
                'host'      => $app->config('database.host'),
                'database'  => $app->config('database.database'),
                'username'  => $app->config('database.user'),
                'password'  => $app->config('database.password'),
                'charset'   => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix'    => '',
            ]);

            return $capsule;
        };

        $app['application.speakers'] = function ($app) {
            return new Speakers(
                $app[IdentityProvider::class]
            );
        };

        $app[AirportInformationDatabase::class] = function () {
            return new Airport();
        };

        $app['security.random'] = function () {
            return new PseudoRandomStringGenerator();
        };
    }
}
