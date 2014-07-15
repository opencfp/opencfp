<?php
/**
 * Object representing Speaker information that is not contained
 * in the Sentry User object
 */

namespace OpenCFP\Model;

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

        $sql = "INSERT INTO speakers (user_id, info, bio, photo_path) VALUES (?, ?, ?, ?)";
        $stmt = $this->_db->prepare($sql);

        return $stmt->execute(array(
            $data['user_id'],
            trim($data['info']),
            trim($data['bio']),
            $data['photo_path']
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
        $sql = "SELECT * FROM speakers WHERE user_id = ?";
        $stmt = $this->_db->prepare($sql);
        $stmt->execute(array($user_id));
        $row = $stmt->fetch();

        if ($row !== false) {
            return $row;
        }

        return false;
    }

    /**
     * Get details for a speaker based on user id
     *
     * @param integer $user_id
     * @return false|array
     */
    public function getDetailsByUserId($user_id)
    {
        $sql = "
            SELECT u.email, u.first_name, u.last_name, u.company, u.twitter, u.airport, s.info, s.bio, s.photo_path
            FROM users u
            LEFT JOIN speakers s ON s.user_id = u.id
            WHERE u.id = ?
        ";
        $stmt = $this->_db->prepare($sql);
        $stmt->execute(array($user_id));
        $row = $stmt->fetch();

        if ($row !== false) {
            return $row;
        }

        return false;
    }

    /**
     * Update an speaker record
     *
     * @param array $speaker_details
     * @return boolean
     */
    public function update($speaker_details)
    {
        // Grab our details and build a comparison
        $details = $this->getDetailsByUserId($speaker_details['user_id']);

        $speakerPhoto = isset($speaker_details['speaker_photo']) ? $speaker_details['speaker_photo'] : $details['photo_path'];

        // Remove old photo if new one has been uploaded
        if ($speakerPhoto !== $details['photo_path']) {
            unlink(UPLOAD_PATH . $details['photo_path']);
        }

        if ($details['first_name'] != $speaker_details['first_name']
            || $details['last_name'] != $speaker_details['last_name']
            || $details['company'] != $speaker_details['company']
            || $details['twitter'] != $speaker_details['twitter']
            || $details['email'] != $speaker_details['email']
            || $details['airport'] != $speaker_details['airport']) {
            $sql = "
                UPDATE users
                SET email = ?,
                first_name = ?,
                last_name = ?,
                company = ?,
                twitter = ?,
                airport = ?
                WHERE id = ?
            ";
            $stmt = $this->_db->prepare($sql);
            $stmt->execute(array(
                trim($speaker_details['email']),
                trim($speaker_details['first_name']),
                trim($speaker_details['last_name']),
                trim($speaker_details['company']),
                trim($speaker_details['twitter']),
                trim($speaker_details['airport']),
                $speaker_details['user_id'])
            );

            if ($stmt->rowCount() !== 1) {
                return false;
            }
        }

        // Do we have an existing record?
        $sql = "SELECT COUNT(info) AS speaker_count FROM speakers WHERE user_id = ?";
        $stmt = $this->_db->prepare($sql);
        $stmt->execute(array($speaker_details['user_id']));
        $row = $stmt->fetch();

        if (isset($row['speaker_count']) && $row['speaker_count'] == 1) {
            // Check if any fields have changed
            if (
                $speaker_details['speaker_info'] == $details['info'] &&
                $speaker_details['speaker_bio'] == $details['bio'] &&
                $speakerPhoto == $details['photo_path']
            ) {
                return true;
            }

            $sql = "
                UPDATE speakers
                SET info = ?,
                bio = ?,
                photo_path = ?
                WHERE user_id = ?
            ";
            $stmt = $this->_db->prepare($sql);
            $stmt->execute(array(
                trim($speaker_details['speaker_info']),
                trim($speaker_details['speaker_bio']),
                $speakerPhoto,
                trim($speaker_details['user_id']))
            );

            if ($stmt->rowCount() !== 1) {
                return false;
            }
        }

        if (isset($row['speaker_count']) && $row['speaker_count'] == 0) {
            $sql = "INSERT INTO speakers (user_id, info, bio, photo_path) VALUES (?, ?, ?, ?)";
            $stmt = $this->_db->prepare($sql);
            return $stmt->execute(array(
                $speaker_details['user_id'],
                trim($speaker_details['speaker_info']),
                trim($speaker_details['speaker_bio']),
                $speakerPhoto
            ));
        }

        return true;
    }

    /**
     * Return an array of all the speakers, ordered by the last name by default
     * by default
     *
     * @param string $orderBy
     * @param string $orderByDirection
     * @internal param string $order default is 'title'
     * @return array
     */
    public function getAll($orderBy = 'last_name', $orderByDirection = 'ASC')
    {
        $sql = "SELECT * FROM users ORDER BY {$orderBy} {$orderByDirection}";
        $stmt = $this->_db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();

        return $results;
    }
    
    /**
     * Get total record count
     */
    public function getTotalRecords()
    {
        $sql = "SELECT COUNT(*) AS total FROM users";
        $stmt = $this->_db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetch();

        return $results['total'];
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

    public function delete($userId)
    {
        // Check to make sure the user exists
        $details = $this->getDetailsByUserId($userId);

        if (!$details) {
            return false;
        }

        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $this->_db->prepare($sql);
        $stmt->execute(array($userId));

        return true;
    }
}
