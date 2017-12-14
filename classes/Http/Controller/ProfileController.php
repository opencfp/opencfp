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

    /**
     * @var PathInterface
     */
    private $path;

    /**
     * ProfileController constructor.
     *
     * @param Authentication        $authentication
     * @param HTMLPurifier          $purifier
     * @param ProfileImageProcessor $profileImageProcessor
     * @param Twig_Environment      $twig
     * @param UrlGeneratorInterface $urlGenerator
     * @param PathInterface         $path
     */
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
        $this->path                  = $path;

        parent::__construct($twig, $urlGenerator);
    }

    public function editAction(Request $request): Response
    {
        $user = $this->authentication->user();

        if ((string) $user->getId() !== $request->get('id')) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => "You cannot edit someone else's profile",
            ]);

            return $this->redirectTo('dashboard');
        }

        $speakerData = User::find($user->getId())->toArray();

        return $this->render('user/edit.twig', [
            'email'          => $user->getLogin(),
            'first_name'     => $speakerData['first_name'],
            'last_name'      => $speakerData['last_name'],
            'company'        => $speakerData['company'],
            'twitter'        => $speakerData['twitter'],
            'url'            => $speakerData['url'],
            'speaker_info'   => $speakerData['info'],
            'speaker_bio'    => $speakerData['bio'],
            'speaker_photo'  => $speakerData['photo_path'],
            'preview_photo'  => $this->path->downloadFromPath() . $speakerData['photo_path'],
            'airport'        => $speakerData['airport'],
            'transportation' => $speakerData['transportation'],
            'hotel'          => $speakerData['hotel'],
            'id'             => $user->getId(),
            'formAction'     => $this->url('user_update'),
            'buttonInfo'     => 'Update Profile',
        ]);
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

    public function passwordAction(): Response
    {
        return $this->render('user/change_password.twig');
    }

    public function passwordProcessAction(Request $request): Response
    {
        $user = $this->authentication->user();

        /**
         * Okay, the logic is kind of weird but we can use the SignupForm
         * validation code to make sure our password changes are good
         */
        $formData = [
            'password'  => $request->get('password'),
            'password2' => $request->get('password_confirm'),
        ];
        $form = new SignupForm($formData, $this->purifier);
        $form->sanitize();

        if ($form->validatePasswords() === false) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => \implode('<br>', $form->getErrorMessages()),
            ]);

            return $this->redirectTo('password_edit');
        }

        $sanitizedData = $form->getCleanData();
        $resetCode     = $user->getResetPasswordCode();

        if (!$user->attemptResetPassword($resetCode, $sanitizedData['password'])) {
            $request->getSession()->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'Unable to update your password in the database. Please try again.',
            ]);

            return $this->redirectTo('password_edit');
        }

        $request->getSession()->set('flash', [
            'type'  => 'success',
            'short' => 'Success',
            'ext'   => 'Changed your password.',
        ]);

        return $this->redirectTo('password_edit');
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
