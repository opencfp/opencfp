<?php

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
                'type' => 'error',
                'short' => 'Error',
                'ext' => "You cannot edit someone else's profile",
            ]);

            return $this->redirectTo('dashboard');
        }

        $speaker_data = User::find($user->getId())->toArray();

        $form_data = [
            'email' => $user->getLogin(),
            'first_name' => $speaker_data['first_name'],
            'last_name' => $speaker_data['last_name'],
            'company' => $speaker_data['company'],
            'twitter' => $speaker_data['twitter'],
            'url' => $speaker_data['url'],
            'speaker_info' => $speaker_data['info'],
            'speaker_bio' => $speaker_data['bio'],
            'speaker_photo' => $speaker_data['photo_path'],
            'preview_photo' => '/uploads/' . $speaker_data['photo_path'],
            'airport' => $speaker_data['airport'],
            'transportation' => $speaker_data['transportation'],
            'hotel' => $speaker_data['hotel'],
            'id' => $user->getId(),
            'formAction' => $this->url('user_update'),
            'buttonInfo' => 'Update Profile',
        ];

        return $this->render('user/edit.twig', $form_data) ;
    }

    public function processAction(Request $req)
    {
        $userId = $this->service(Authentication::class)->userId();

        if ((string) $userId !== $req->get('id')) {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => "You cannot edit someone else's profile",
            ]);

            return $this->redirectTo('dashboard');
        }

        $form_data = $this->getFormData($req);

        if ($req->files->get('speaker_photo') != null) {
            $form_data['speaker_photo'] = $req->files->get('speaker_photo');
        }

        $form = new SignupForm($form_data, $this->service('purifier'));
        $isValid = $form->validateAll('update');

        if ($isValid) {
            $sanitized_data = $this->transformSanitizedData($form->getCleanData());
            if (isset($form_data['speaker_photo'])) {
                $sanitized_data['photo_path'] = $this->service('profile_image_processor')
                    ->process($form_data['speaker_photo']);
            }
            unset($sanitized_data['speaker_photo']);
            User::find($userId)->update($sanitized_data);

            return $this->redirectTo('dashboard');
        }
        $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => implode('<br>', $form->getErrorMessages()),
            ]);

        $form_data['formAction'] = $this->url('user_update');
        $form_data['buttonInfo'] = 'Update Profile';
        $form_data['id'] = $userId;
        $form_data['flash'] = $this->service('session')->get('flash');

        return $this->render('user/edit.twig', $form_data);
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
            'password' => $req->get('password'),
            'password2' => $req->get('password_confirm'),
        ];
        $form = new SignupForm($formData, $this->service('purifier'));
        $form->sanitize();

        if ($form->validatePasswords() === false) {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => implode('<br>', $form->getErrorMessages()),
            ]);

            return $this->redirectTo('password_edit');
        }

        /**
         * Resetting passwords looks weird because we need to use Sentry's
         * own built-in password reset functionality to do it
         */
        $sanitized_data = $form->getCleanData();
        $reset_code = $user->getResetPasswordCode();

        if (! $user->attemptResetPassword($reset_code, $sanitized_data['password'])) {
            $this->service('session')->set('flash', [
                'type' => 'error',
                'short' => 'Error',
                'ext' => 'Unable to update your password in the database. Please try again.',
            ]);

            return $this->redirectTo('password_edit');
        }

        $this->service('session')->set('flash', [
            'type' => 'success',
            'short' => 'Success',
            'ext' => 'Changed your password.',
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
        $form_data = [
            'email' => $req->get('email'),
            'user_id' => $req->get('id'),
            'first_name' => $req->get('first_name'),
            'last_name' => $req->get('last_name'),
            'company' => $req->get('company'),
            'twitter' => $req->get('twitter'),
            'url' => $req->get('url'),
            'airport' => $req->get('airport'),
            'transportation' => (int) $req->get('transportation'),
            'hotel' => (int) $req->get('hotel'),
            'speaker_info' => $req->get('speaker_info') ?: null,
            'speaker_bio' => $req->get('speaker_bio') ?: null,
        ];

        return $form_data;
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
        $sanitizedData['twitter'] = preg_replace('/^@/', '', $sanitizedData['twitter']);

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
