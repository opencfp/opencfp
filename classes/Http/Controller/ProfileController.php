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

namespace OpenCFP\Http\Controller;

use HTMLPurifier;
use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Domain\Services\ProfileImageProcessor;
use OpenCFP\Http\Form\SignupForm;
use OpenCFP\PathInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig_Environment;

class ProfileController extends BaseController
{
    /**
     * @var Authentication
     */
    private $authentication;

    /**
     * @var HTMLPurifier
     */
    private $purifier;

    /**
     * @var ProfileImageProcessor
     */
    private $profileImageProcessor;

    public function __construct(
        Authentication $authentication,
        HTMLPurifier $purifier,
        ProfileImageProcessor $profileImageProcessor,
        Twig_Environment $twig,
        UrlGeneratorInterface $urlGenerator,
        PathInterface $path
    ) {
        $this->authentication        = $authentication;
        $this->purifier              = $purifier;
        $this->profileImageProcessor = $profileImageProcessor;

        parent::__construct($twig, $urlGenerator);
    }

    public function processAction(Request $request): Response
    {
        $userId = $this->authentication->user()->getId();

        if ((string) $userId !== $request->get('id')) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => "You cannot edit someone else's profile",
            ]);

            return $this->redirectTo('dashboard');
        }

        $formData = $this->getFormData($request);

        if ($request->files->get('speaker_photo') != null) {
            $formData['speaker_photo'] = $request->files->get('speaker_photo');
        }

        $form    = new SignupForm($formData, $this->purifier);
        $isValid = $form->validateAll('update');

        if ($isValid) {
            $sanitizedData = $this->transformSanitizedData($form->getCleanData());

            if (isset($formData['speaker_photo'])) {
                $sanitizedData['photo_path'] = $this->profileImageProcessor->process($formData['speaker_photo']);
            }
            unset($sanitizedData['speaker_photo']);
            User::find($userId)->update($sanitizedData);

            return $this->redirectTo('dashboard');
        }
        $request->getSession()->set('flash', [
            'type'  => 'error',
            'short' => 'Error',
            'ext'   => \implode('<br>', $form->getErrorMessages()),
        ]);

        return $this->render('user/edit.twig', \array_merge($formData, [
            'formAction' => $this->url('user_update'),
            'buttonInfo' => 'Update Profile',
            'id'         => $userId,
            'flash'      => $request->getSession()->get('flash'),
        ]));
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    private function getFormData(Request $request): array
    {
        $formData = [
            'email'          => $request->get('email'),
            'user_id'        => $request->get('id'),
            'first_name'     => $request->get('first_name'),
            'last_name'      => $request->get('last_name'),
            'company'        => $request->get('company'),
            'twitter'        => $request->get('twitter'),
            'url'            => $request->get('url'),
            'airport'        => $request->get('airport'),
            'transportation' => (int) $request->get('transportation'),
            'hotel'          => (int) $request->get('hotel'),
            'speaker_info'   => $request->get('speaker_info') ?: null,
            'speaker_bio'    => $request->get('speaker_bio') ?: null,
        ];

        return $formData;
    }

    /**
     * Transforms the sanitized data array to be used by our User Model for updates
     *
     * @param array $sanitizedData
     *
     * @return array
     */
    private function transformSanitizedData(array $sanitizedData): array
    {
        // Remove leading @ for twitter
        $sanitizedData['twitter'] = \preg_replace('/^@/', '', $sanitizedData['twitter']);

        $sanitizedData['bio'] = $sanitizedData['speaker_bio'];
        unset($sanitizedData['speaker_bio']);
        $sanitizedData['info'] = $sanitizedData['speaker_info'];
        unset($sanitizedData['speaker_info']);
        $sanitizedData['id'] = $sanitizedData['user_id'];
        unset($sanitizedData['user_id']);
        $sanitizedData['has_made_profile'] = 1;

        return $sanitizedData;
    }
}
