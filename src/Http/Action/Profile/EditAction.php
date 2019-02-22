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

namespace OpenCFP\Http\Action\Profile;

use OpenCFP\Domain\Model;
use OpenCFP\Domain\Services;
use OpenCFP\PathInterface;
use Symfony\Component\HttpFoundation;
use Symfony\Component\Routing;
use Twig_Environment;

final class EditAction
{
    /**
     * @var Services\Authentication
     */
    private $authentication;

    /**
     * @var PathInterface
     */
    private $path;

    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @var Routing\Generator\UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(
        Services\Authentication $authentication,
        PathInterface $path,
        Twig_Environment $twig,
        Routing\Generator\UrlGeneratorInterface $urlGenerator
    ) {
        $this->authentication = $authentication;
        $this->path           = $path;
        $this->twig           = $twig;
        $this->urlGenerator   = $urlGenerator;
    }

    public function __invoke(HttpFoundation\Request $request): HttpFoundation\Response
    {
        $user = $this->authentication->user();

        if ((string) $user->getId() !== $request->get('id')) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => "You cannot edit someone else's profile",
            ]);

            $url = $this->urlGenerator->generate('dashboard');

            return new HttpFoundation\RedirectResponse($url);
        }

        $speakerData = Model\User::find($user->getId())->toArray();

        $content = $this->twig->render('user/edit.twig', [
            'email'            => $user->getLogin(),
            'first_name'       => $speakerData['first_name'],
            'last_name'        => $speakerData['last_name'],
            'company'          => $speakerData['company'],
            'twitter'          => $speakerData['twitter'],
            'joindin_username' => $speakerData['joindin_username'],
            'url'              => $speakerData['url'],
            'speaker_info'     => $speakerData['info'],
            'speaker_bio'      => $speakerData['bio'],
            'speaker_photo'    => $speakerData['photo_path'],
            'preview_photo'    => $this->path->uploadPath() . $speakerData['photo_path'],
            'airport'          => $speakerData['airport'],
            'transportation'   => $speakerData['transportation'],
            'hotel'            => $speakerData['hotel'],
            'id'               => $user->getId(),
            'formAction'       => $this->urlGenerator->generate('user_update'),
            'buttonInfo'       => 'Update Profile',
        ]);

        return new HttpFoundation\Response($content);
    }
}
