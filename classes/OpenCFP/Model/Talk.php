<?php

namespace OpenCFP\Model;

class Talk
{
    protected $_db;

    /**
     * Constructor for the class
     *
     * @param PDO $db
     */
    public function __construct($db)
    {
        $this->_db = $db;
    }

    /**
     * Create a talk when you pass new data in
     *
     * @param array $data
     * @return boolean
     */
    public function create($data)
    {
        $sql = 'INSERT INTO `talks` (`title`, `description`, `type`, `user_id`) VALUES (:title, :description, :type, :user)';

        $stmt = $this->_db->prepare($sql);
        $stmt->bindValue(':title', $data['title']);
        $stmt->bindValue(':description', $data['description']);
        $stmt->bindValue(':type', $data['type']);
        $stmt->bindValue(':user', $data['user_id'], \PDO::PARAM_INT);
        $stmt->execute();

        return 1 === (int) $stmt->rowCount();
    }

    /**
     * Return a record that matches an ID passed into it
     *
     * @param int $id
     * @return array|false
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM talks WHERE id = ?";
        $stmt = $this->_db->prepare($sql);
        $stmt->execute(array($id));

        return $stmt->fetch() ?: false;
    }

    /**
     * Returns a user's talk.
     *
     * @param int $id The talk's primary key
     * @param int $user The user's primary key
     * @return array|false
     */
    public function findUserTalk($id, $user)
    {
        $stmt = $this->_db->prepare('SELECT * FROM talks WHERE id = :id AND user_id = :user');
        $stmt->bindValue(':id', (int) $id, \PDO::PARAM_INT);
        $stmt->bindValue(':user', (int) $user, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Return one or more Talk records that match a user ID passed in
     *
     * @param integer $userId
     * @return false|array
     */
    public function findByUserId($userId)
    {
        $sql = "SELECT * FROM talks WHERE user_id = ?";
        $stmt = $this->_db->prepare($sql);
        $stmt->execute(array($userId));

        return $stmt->fetchAll();

    }

    /**
     * Update a record using data passed in to it
     *
     * @param array $data
     * @return boolean
     */
    public function update($data)
    {
        if (empty($data['id'])) {
            return false;
        }

        $sql = 'UPDATE `talks` SET `title` = :title, `description` = :description, `type` = :type';
        $sql.= ' WHERE `id` = :id AND `user_id` = :user';

        $stmt = $this->_db->prepare($sql);
        $stmt->bindValue(':title', $data['title']);
        $stmt->bindValue(':description', $data['description']);
        $stmt->bindValue(':type', $data['type']);
        $stmt->bindValue(':id', $data['id'], \PDO::PARAM_INT);
        $stmt->bindValue(':user', $data['user_id'], \PDO::PARAM_INT);
        $stmt->execute();

        return 1 === (int) $stmt->rowCount();
    }

    /**
     * Delete a Talk record given a talk ID and a user ID
     *
     * @param integer $talkId
     * @param integer $userId
     * @return boolean
     */
    public function delete($talkId, $userId)
    {
        $stmt = $this->_db->prepare('DELETE FROM talks WHERE id = :id AND user_id = :user');
        $stmt->bindValue(':id', (int) $talkId, \PDO::PARAM_INT);
        $stmt->bindValue(':user', (int) $userId, \PDO::PARAM_INT);
        $stmt->execute();

        return 1 === (int) $stmt->rowCount();
    }
}
