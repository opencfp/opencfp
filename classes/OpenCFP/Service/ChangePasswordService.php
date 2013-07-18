<?php

namespace OpenCFP\Service;

use Cartalyst\Sentry\Sentry;
use OpenCFP\Form\ChangePasswordForm;
use OpenCFP\Model\Speaker;

class ChangePasswordService
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
     * Changes the user's password.
     *
     * @param ChangePasswordForm $form
     * @return mixed
     * @throws \Exception|\PDOException
     */
    public function changeUserPassword(ChangePasswordForm $form)
    {
        $user = $this->_sentry->getUser();
        $gateway = $this->_createSpeaker();

        $this->_db->beginTransaction();
        try {
            $gateway->changePassword($form->getPassword(), $user);
            $this->_db->commit();
        } catch (\PDOException $e) {
            $this->_db->rollBack();
            throw $e;
        }

        return true;
    }

    /**
     * Returns a new change password form.
     *
     * @return ChangePasswordForm
     */
    public function createChangePasswordForm()
    {
        return new ChangePasswordForm($this->_purifier);
    }

    /**
     * Creates a new Speaker gateway instance.
     *
     * @return Speaker
     */
    private function _createSpeaker()
    {
        return new Speaker($this->_db);
    }
}