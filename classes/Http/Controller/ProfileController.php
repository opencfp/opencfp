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

use OpenCFP\Domain\Model\User;
use OpenCFP\Domain\Services\Authentication;
use OpenCFP\Http\Form\SignupForm;
use Symfony\Component\HttpFoundation\Request;

class ProfileController extends BaseController
{
    public function editAction(Request $req)
    {
        $user = $this->service(Authentication::class)->user();

        if ((string) $user->getId() !== $req->get('id')) {
            $this->service('session')->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => "You cannot edit someone else's profile",
            ]);

            return $this->redirectTo('dashboard');
        }

        $speakerData = User::find($user->getId())->toArray();

        $formData = [
            'email'          => $user->getLogin(),
            'first_name'     => $speakerData['first_name'],
            'last_name'      => $speakerData['last_name'],
            'company'        => $speakerData['company'],
            'twitter'        => $speakerData['twitter'],
            'url'            => $speakerData['url'],
            'speaker_info'   => $speakerData['info'],
            'speaker_bio'    => $speakerData['bio'],
            'speaker_photo'  => $speakerData['photo_path'],
            'preview_photo'  => '/uploads/' . $speakerData['photo_path'],
            'airport'        => $speakerData['airport'],
            'transportation' => $speakerData['transportation'],
            'hotel'          => $speakerData['hotel'],
            'id'             => $user->getId(),
            'formAction'     => $this->url('user_update'),
            'buttonInfo'     => 'Update Profile',
        ];

        return $this->render('user/edit.twig', $formData);
    }

    public function processAction(Request $req)
    {
        $userId = $this->service(Authentication::class)->userId();

        if ((string) $userId !== $req->get('id')) {
            $this->service('session')->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => "You cannot edit someone else's profile",
            ]);

            return $this->redirectTo('dashboard');
        }

        $formData = $this->getFormData($req);

        if ($req->files->get('speaker_photo') != null) {
            $formData['speaker_photo'] = $req->files->get('speaker_photo');
        }

        $form    = new SignupForm($formData, $this->service('purifier'));
        $isValid = $form->validateAll('update');

        if ($isValid) {
            $sanitizedData = $this->transformSanitizedData($form->getCleanData());
            if (isset($formData['speaker_photo'])) {
                $sanitizedData['photo_path'] = $this->service('profile_image_processor')
                    ->process($formData['speaker_photo']);
            }
            unset($sanitizedData['speaker_photo']);
            User::find($userId)->update($sanitizedData);

            return $this->redirectTo('dashboard');
        }
        $this->service('session')->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => \implode('<br>', $form->getErrorMessages()),
            ]);

        $formData['formAction'] = $this->url('user_update');
        $formData['buttonInfo'] = 'Update Profile';
        $formData['id']         = $userId;
        $formData['flash']      = $this->service('session')->get('flash');

        return $this->render('user/edit.twig', $formData);
    }

    public function passwordAction()
    {
        return $this->render('user/change_password.twig');
    }

    public function passwordProcessAction(Request $req)
    {
        $user = $this->service(Authentication::class)->user();

        /**
         * Okay, the logic is kind of weird but we can use the SignupForm
         * validation code to make sure our password changes are good
         */
        $formData = [
            'password'  => $req->get('password'),
            'password2' => $req->get('password_confirm'),
        ];
        $form = new SignupForm($formData, $this->service('purifier'));
        $form->sanitize();

        if ($form->validatePasswords() === false) {
            $this->service('session')->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => \implode('<br>', $form->getErrorMessages()),
            ]);

            return $this->redirectTo('password_edit');
        }

        $sanitizedData = $form->getCleanData();
        $resetCode     = $user->getResetPasswordCode();

        if (!$user->attemptResetPassword($resetCode, $sanitizedData['password'])) {
            $this->service('session')->set('flash', [
                'type'  => 'error',
                'short' => 'Error',
                'ext'   => 'Unable to update your password in the database. Please try again.',
            ]);

            return $this->redirectTo('password_edit');
        }

        $this->service('session')->set('flash', [
            'type'  => 'success',
            'short' => 'Success',
            'ext'   => 'Changed your password.',
        ]);

        return $this->redirectTo('password_edit');
    }

    /**
     * @param Request $req
     *
     * @return array
     */
    private function getFormData(Request $req): array
    {
        $formData = [
            'email'          => $req->get('email'),
            'user_id'        => $req->get('id'),
            'first_name'     => $req->get('first_name'),
            'last_name'      => $req->get('last_name'),
            'company'        => $req->get('company'),
            'twitter'        => $req->get('twitter'),
            'url'            => $req->get('url'),
            'airport'        => $req->get('airport'),
            'transportation' => (int) $req->get('transportation'),
            'hotel'          => (int) $req->get('hotel'),
            'speaker_info'   => $req->get('speaker_info') ?: null,
            'speaker_bio'    => $req->get('speaker_bio') ?: null,
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
