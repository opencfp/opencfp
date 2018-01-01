<?php

declare(strict_types=1);

/**
 * Copyright (c) 2013-2018 OpenCFP
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/opencfp/opencfp
 */

namespace OpenCFP\Http\Action\Signup;

use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation;
use Illuminate\Validation;
use OpenCFP\Domain\Services;
use OpenCFP\Domain\ValidationException;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;
use Twig_Environment;

final class ProcessAction
{
    /**
     * @var Services\Authentication
     */
    private $authentication;

    /**
     * @var Services\AccountManagement
     */
    private $accounts;

    /**
     * @var Routing\Generator\UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(
        Services\Authentication $authentication,
        Services\AccountManagement $accounts,
        Twig_Environment $twig,
        Routing\Generator\UrlGeneratorInterface $urlGenerator
    ) {
        $this->authentication = $authentication;
        $this->accounts       = $accounts;
        $this->urlGenerator   = $urlGenerator;
    }

    public function __invoke(HttpFoundation\Request $request): HttpFoundation\Response
    {
        try {
            $this->validate($request, [
                'email'    => 'required|email',
                'password' => 'required',
                'coc'      => 'accepted',
            ]);

            $this->accounts->create(
                $request->get('email'),
                $request->get('password'),
                [
                    'activated' => 1,
                ]
            );

            $this->accounts->activate($request->get('email'));

            $request->getSession()->set('flash', [
                'type'  => 'success',
                'short' => 'Success',
                'ext'   => "You've successfully created your account!",
            ]);

            $this->authentication->authenticate(
                $request->get('email'),
                $request->get('password')
            );

            $url = $this->urlGenerator->generate('dashboard');

            return new HttpFoundation\RedirectResponse($url);
        } catch (ValidationException $e) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => $e->getMessage(),
                'ext'   => $e->errors(),
            ]);

            return new HttpFoundation\RedirectResponse($request->headers->get('referer'));
        } catch (\RuntimeException $e) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'A user already exists with that email address',
            ]);

            return new HttpFoundation\RedirectResponse($request->headers->get('referer'));
        }
    }

    /**
     * @param HttpFoundation\Request $request
     * @param array                  $rules
     * @param array                  $messages
     * @param array                  $customAttributes
     *
     * @throws ValidationException
     */
    private function validate(HttpFoundation\Request $request, $rules = [], $messages = [], $customAttributes = [])
    {
        $data = $request->query->all() + $request->request->all() + $request->files->all();

        $validation = new Validation\Factory(
            new Translation\Translator(
                new Translation\FileLoader(
                    new Filesystem(),
                    __DIR__ . '/../../../resources/lang'
                ),
                'en'
            ),
            new Container()
        );

        $validator = $validation->make(
            $data,
            $rules,
            $messages,
            $customAttributes
        );

        if ($validator->fails()) {
            throw ValidationException::withErrors(array_flatten($validator->errors()->toArray()));
        }

        unset($validation, $validator);
    }
}
