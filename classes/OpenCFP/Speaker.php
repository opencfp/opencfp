<?php
/**
 * Object representing Speaker information that is not contained
 * in the Sentry User object
 */

namespace OpenCFP;

class Speaker
{
    protected $_db;

    /**
     * Constructor for object
     *
     * @param PDO $db
     */
    public function __construct($db)
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
        if (empty($data['user_id']) || empty($data['info'])) {
            return false;
        }

        $sql = "INSERT INTO speakers (user_id, info) VALUES (?, ?)";
        $stmt = $this->_db->prepare($sql);

        return $stmt->execute(array(
            $data['user_id'],
            $data['info']
        )
    );
    }

    /**
     * Find info for a speaker given a known Sentry User if
     *
     * @param integer $user_id
     * @return false|array
     */
    public function findByUserId($user_id)
    {
        if ((int)$user_id != $user_id) {
            return false;
        }    

        $sql = "SELECT info FROM speakers WHERE user_id = ?";
        $stmt = $this->_db->prepare($sql);
        $stmt->execute(array($user_id));
        $row = $stmt->fetch();

        if ($row !== false) {
            return $row;
        }

        return false;
    }
}
