<?php

namespace OpenCFP\Service;

use Cartalyst\Sentry\Users\UserExistsException;
use OpenCFP\Form\SignupForm;
use OpenCFP\Model\Speaker;
use OpenCFP\Model\UserManagerInterface;

/**
 * This service class manages business and persistence logic
 * related to speaker profiles.
 *
 * @package OpenCFP\Service
 * @author Hugo Hamon
 */
class RegistrationService
{
    private $_db;
    private $_userManager;
    private $_purifier;

    /**
     * Constructor.
     *
     * @param \PDO $db
     * @param UserManagerInterface $userManager
     * @param \HTMLPurifier $purifier
     */
    public function __construct(\PDO $db, UserManagerInterface $userManager, \HTMLPurifier $purifier)
    {
        $this->_db = $db;
        $this->_userManager = $userManager;
        $this->_purifier = $purifier;
    }

    /**
     * Creates a Sentry user account and a Speaker profile.
     *
     * @param SignupForm $form
     * @return bool|Speaker
     * @throws \RuntimeException
     * @throws \Exception
     */
    public function createUserAccount(SignupForm $form)
    {
        $data = $form->getSpeakerData();

        if (!$group = $this->_userManager->getGroup('Speakers')) {
            throw new \RuntimeException('Unable to find Speakers group.');
        }

        try {
            $this->_db->beginTransaction();
            $user = $this->_userManager->createUser($data['user']);
            $user->addGroup($group);

            $speaker = new Speaker($this->_db);
            $speaker->create(array_merge($data['speaker'], array('user_id' => $user->getId())));

            $this->_db->commit();
        } catch (UserExistsException $e) {
            $form->addError('A user already exists with that email address');
            $this->_db->rollBack();
            return false;
        } catch (\Exception $e) {
            $this->_db->rollBack();
            throw $e;
        }

        return $speaker;
    }

    /**
     * Creates a signup form instance.
     *
     * @param array $options An array of form options
     * @return SignupForm
     */
    public function createSignupForm(array $options = array())
    {
        return new SignupForm($this->_purifier, $options);
    }

}