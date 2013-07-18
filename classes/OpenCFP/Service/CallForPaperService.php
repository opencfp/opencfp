<?php

namespace OpenCFP\Service;

use Cartalyst\Sentry\Sentry;
use OpenCFP\Model\Talk;
use OpenCFP\Form\TalkForm;

/**
 * This service class manages business and persistence logic
 * related to talks submission.
 *
 * @package OpenCFP\Service
 * @author Hugo Hamon
 */
class CallForPaperService
{
    /**
     * The PDO instance.
     *
     * @var \PDO
     */
    private $_db;

    /**
     * The Sentry instance.
     *
     * @var \Cartalyst\Sentry\Sentry
     */
    private $_sentry;

    /**
     * The HTML purifier instance.
     *
     * @var \HTMLPurifier
     */
    private $_purifier;

    public function __construct(\PDO $db, Sentry $sentry, \HTMLPurifier $purifier)
    {
        $this->_db = $db;
        $this->_sentry = $sentry;
        $this->_purifier = $purifier;
    }

    /**
     * Submit a new talk proposal.
     *
     * @param TalkForm $form
     * @return bool $result
     */
    public function submitTalkProposal(TalkForm $form)
    {
        $gateway = $this->_createTalk();
        $user    = $this->_getUser();
        $data    = $form->getData();

        $data['user_id'] = $user->getId();

        $this->_db->beginTransaction();
        try {
            $result = $gateway->create($data);
            $this->_db->commit();
        } catch (\PDOException $e) {
            $this->_db->rollBack();
            throw $e;
        }

        return $result;
    }

    /**
     * Edits the current talk proposal.
     *
     * @param TalkForm $form
     * @return bool
     */
    public function updateTalkProposal(TalkForm $form)
    {
        $data = $form->getData();
        $gateway = $this->_createTalk();

        $this->_db->beginTransaction();
        try {
            $result = $gateway->update($data);
            $this->_db->commit();
        } catch (\PDOException $e) {
            $this->_db->rollBack();
            throw $e;
        }

        return $result;
    }

    /**
     * Cancels a talk proposal.
     *
     * @param int $id The talk proposal primary key
     * @return bool $result
     * @throws \Exception|\PDOException
     */
    public function cancelTalkProposal($id)
    {
        $user = $this->_getUser();
        $talk = $this->_createTalk();

        $result = false;
        $this->_db->beginTransaction();
        try {
            $result = $talk->delete($id, $user->getId());
            $this->_db->commit();
        } catch (\PDOException $e) {
            $this->_db->rollBack();
            throw $e;
        }

        return $result;
    }

    /**
     * Finds the current's user talk identified by its primary key.
     *
     * @param int $id The talk's primary key
     * @return array|false
     */
    public function find($id)
    {
        $gateway = $this->_createTalk();
        $user = $this->_getUser();

        return $gateway->findUserTalk($id, $user->getId());
    }

    /**
     * Returns a new TalkForm instance.
     *
     * @param array $data The default data to populate the form
     * @return TalkForm
     */
    public function createTalkForm(array $data = array())
    {
        $form = new TalkForm($this->_purifier);
        $form->populate($data);

        return $form;
    }

    /**
     * Returns the current logged-in user representation.
     *
     * @return \Cartalyst\Sentry\Cartalyst\Sentry\Users\UserInterface
     */
    private function _getUser()
    {
        return $this->_sentry->getUser();
    }

    /**
     * Returns a new Talk.
     *
     * @return \OpenCFP\Model\Talk
     */
    private function _createTalk()
    {
        return new Talk($this->_db);
    }
}