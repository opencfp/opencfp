<?php

namespace OpenCFP\Service;

use Cartalyst\Sentry\Sentry;
use OpenCFP\Form\ProfileForm;
use OpenCFP\Model\Speaker;

/**
 * This service class manages business and persistence logic
 * related to speaker profiles.
 *
 * @package OpenCFP\Service
 * @author Hugo Hamon
 */
class ProfileService
{
    private $_db;
    private $_sentry;
    private $_purifier;

    /**
     * Constructor.
     *
     * @param \PDO $db
     * @param Sentry $sentry
     * @param \HTMLPurifier $purifier
     */
    public function __construct(\PDO $db, Sentry $sentry, \HTMLPurifier $purifier)
    {
        $this->_db = $db;
        $this->_sentry = $sentry;
        $this->_purifier = $purifier;
    }

    /**
     * Updates the speaker profile.
     *
     * @param ProfileForm $form
     */
    public function updateProfile(ProfileForm $form)
    {
        $userDataKeys    = array('email', 'first_name', 'last_name');
        $speakerDataKeys = array('info', 'bio');

        $originalData = $this->getSpeakerProfile();
        $speakerData  = $form->getData($speakerDataKeys);
        $userData     = $form->getData($userDataKeys);

        $originalUserData    = $this->extractData($originalData, $userDataKeys);
        $originalSpeakerData = $this->extractData($originalData, $speakerDataKeys);

        $userModified   = $this->isRecordModified($userData, $originalUserData);
        $speakerModifed = $this->isRecordModified($speakerData, $originalSpeakerData);

        $result = false;
        if (!$userModified && !$speakerModifed) {
            return $result;
        }

        $gateway = $this->_createSpeaker();
        $user = $this->_getUser();

        $data = $form->getData();
        $data['user_id'] = $user->getId();

        $this->_db->beginTransaction();
        try {
            // Update user data only if needed
            if ($userModified) {
                $result = $gateway->updateUser($data);
            }

            // Update speaker data only if needed
            if ($speakerModifed) {
                $result = $gateway->updateSpeaker($data);
            }
            $this->_db->commit();
        } catch (\PDOException $e) {
            $this->_db->rollBack();
            throw $e;
        }

        return $result;
    }

    /**
     * Returns whether or not a record is modified.
     *
     * @param array $newData The new data array
     * @param array $oldData The old data array
     * @return bool
     */
    private function isRecordModified(array $newData, array $oldData)
    {
        $diff = array_diff($newData, $oldData);

        return count($diff) > 0;
    }

    /**
     * Extracts only some data from a an array based on a list of keys.
     *
     * @param array  $data The data list from which to extract data
     * @param array  $keys The keys of data to extract
     * @return array $out  The extracted data
     */
    private function extractData(array $data, array $keys)
    {
        $out = array();
        foreach ($keys as $key) {
            $out[$key] = $data[$key];
        }

        return $out;
    }

    /**
     * Returns the current speaker profile.
     *
     * @return array $profile The speaker's profile
     */
    public function getSpeakerProfile()
    {
        if (!$user = $this->_getUser()) {
            throw new \RuntimeException('The current user is not logged in.');
        }

        $gateway = $this->_createSpeaker();
        if (!$profile = $gateway->findByUserId($user->getId())) {
            throw new \RuntimeException(sprintf('Unable to fetch speaker profile for current logged-in user identified by "%s".', $user->getLoginName()));
        }

        return array_merge($profile, array(
            'email' => $user->getLogin(),
            'user'  => $user,
        ));
    }

    /**
     * Returns a new ProfileForm instance.
     *
     * @param array $data The data to populate the form.
     * @return ProfileForm
     */
    public function createProfileForm(array $data)
    {
        $form = new ProfileForm($this->_purifier);
        $form->populate($data);

        return $form;
    }

    /**
     * Returns the current logged-in user representation.
     *
     * @return \Cartalyst\Sentry\Users\UserInterface
     */
    private function _getUser()
    {
        return $this->_sentry->getUser();
    }

    /**
     * Returns a Speaker gateway.
     *
     * @return Speaker
     */
    private function _createSpeaker()
    {
        return new Speaker($this->_db);
    }
}