<?php

namespace OpenCFP\Model;

class Speaker
{
    protected $_db;

    /**
     * Constructor for object
     *
     * @param \PDO $db The database connection
     */
    public function __construct(\PDO $db)
    {
        $this->_db = $db;
    }

    /**
     * Create new Speaker record in the database
     *
     * @param array $data
     * @return boolean
     */
    public function create($data)
    {
        /**
         * Records must have a user ID to associate with, some speaker info
         * but bio info is optional
         */
        if (empty($data['user_id']) || empty($data['info'])) {
            return false;
        }

        if (!isset($data['bio'])) {
            $data['bio'] = null;
        }

        $stmt = $this->_db->prepare('INSERT INTO speakers (user_id, info, bio) VALUES (:user_id, :info, :bio)');
        $stmt->bindValue(':user_id', $data['user_id'], \PDO::PARAM_INT);
        $stmt->bindValue(':info', $data['info']);
        $stmt->bindValue(':bio', $data['bio']);

        return $stmt->execute();
    }

    /**
     * Find info for a speaker given a known Sentry User if
     *
     * @param integer $user
     * @return false|array
     */
    public function findByUserId($user)
    {
        $sql = 'SELECT s.*, u.`email`, u.`first_name`, u.`last_name`';
        $sql.= ' FROM `speakers` AS `s`';
        $sql.= ' LEFT JOIN `users` AS `u` ON s.`user_id` = u.`id`';
        $sql.= ' WHERE s.`user_id` = :user';

        $stmt = $this->_db->prepare($sql);
        $stmt->bindValue(':user', $user, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Update speaker info.
     *
     * @param array $data
     * @return boolean
     */
    public function updateUser(array $data)
    {
        $sql = 'UPDATE `users`';
        $sql.= ' SET `email` = :email, `first_name` = :firstName, `last_name` = :lastName';
        $sql.= ' WHERE `id` = :id';

        $stmt = $this->_db->prepare($sql);
        $stmt->bindValue(':email', $data['email']);
        $stmt->bindValue(':firstName', $data['first_name']);
        $stmt->bindValue(':lastName', $data['last_name']);
        $stmt->bindValue(':id', $data['user_id'], \PDO::PARAM_INT);
        $stmt->execute();

        return 1 === (int) $stmt->rowCount();
    }

    /**
     * Updates a user record.
     *
     * @param array $data
     * @return boolean
     */
    public function updateSpeaker(array $data)
    {
        $sql = 'UPDATE `speakers`';
        $sql.= ' SET `info` = :info, `bio` = :bio';
        $sql.= ' WHERE `user_id` = :id';

        $stmt = $this->_db->prepare($sql);
        $stmt->bindValue(':info', $data['info']);
        $stmt->bindValue(':bio', $data['bio']);
        $stmt->bindValue(':id', $data['user_id'], \PDO::PARAM_INT);
        $stmt->execute();

        return 1 === (int) $stmt->rowCount();
    }

    public function changePassword($new_password, $user)
    {
        /**
         * This also appears kind of weird, because we use Sentry's own built-in
         * password reset functionality to accomplish the task...
         */
        $reset_code = $user->getResetPasswordCode();

        return $user->attemptResetPassword($reset_code, $new_password);
    }
}
