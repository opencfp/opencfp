<?php
namespace OpenCFP\Http\Form\Entity;

class User
{
    protected $email;
    protected $password;
    protected $permissions;
    protected $last_login;
    protected $first_name;
    protected $last_name;
    protected $created_at;
    protected $updated_at;
    protected $company;
    protected $twitter;
    protected $airport;
    protected $url;
    protected $transportation;
    protected $hotel;
    protected $bio;
    protected $info;
    protected $photo_path;
    protected $agree_coc;

    public function createArray()
    {
        return [
            'email' => $this->getEmail(),
            'password' => $this->getPassword(),
            'permissions' => $this->getPermissions(),
            'last_login' => $this->getLastLogin(),
            'first_name' => $this->getFirstName(),
            'last_name' => $this->getLastName(),
            'created_at' => $this->getCreatedAt(),
            'updated_at' => $this->getUpdatedAt(),
            'company' => $this->getCompany(),
            'twitter' => $this->getTwitter(),
            'airport' => $this->getAirport(),
            'url' => $this->getUrl(),
            'transportation' => $this->getTransportation(),
            'hotel' => $this->getHotel(),
            'bio' => $this->getBio(),
            'info' => $this->getInfo(),
            'photo_path' => $this->getPhotoPath(),
            'agree_coc' => $this->getAgreeCoc(),
        ];
    }


    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @param mixed $permissions
     */
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;
    }

    /**
     * @return mixed
     */
    public function getLastLogin()
    {
        return $this->last_login;
    }

    /**
     * @param mixed $last_login
     */
    public function setLastLogin($last_login)
    {
        $this->last_login = $last_login;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * @param mixed $first_name
     */
    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * @param mixed $last_name
     */
    public function setLastName($last_name)
    {
        $this->last_name = $last_name;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param mixed $created_at
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * @param mixed $updated_at
     */
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
    }

    /**
     * @return mixed
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param mixed $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

    /**
     * @return mixed
     */
    public function getTwitter()
    {
        return $this->twitter;
    }

    /**
     * @param mixed $twitter
     */
    public function setTwitter($twitter)
    {
        $this->twitter = $twitter;
    }

    /**
     * @return mixed
     */
    public function getAirport()
    {
        return $this->airport;
    }

    /**
     * @param mixed $airport
     */
    public function setAirport($airport)
    {
        $this->airport = $airport;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getTransportation()
    {
        return $this->transportation;
    }

    /**
     * @param mixed $transportation
     */
    public function setTransportation($transportation)
    {
        $this->transportation = $transportation;
    }

    /**
     * @return mixed
     */
    public function getHotel()
    {
        return $this->hotel;
    }

    /**
     * @param mixed $hotel
     */
    public function setHotel($hotel)
    {
        $this->hotel = $hotel;
    }

    /**
     * @return mixed
     */
    public function getBio()
    {
        return $this->bio;
    }

    /**
     * @param mixed $bio
     */
    public function setBio($bio)
    {
        $this->bio = $bio;
    }

    /**
     * @return mixed
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * @param mixed $info
     */
    public function setInfo($info)
    {
        $this->info = $info;
    }

    /**
     * @return mixed
     */
    public function getPhotoPath()
    {
        return $this->photo_path;
    }

    /**
     * @param mixed $photo_path
     */
    public function setPhotoPath($photo_path)
    {
        $this->photo_path = $photo_path;
    }

    /**
     * @return mixed
     */
    public function getAgreeCoc()
    {
        return $this->agree_coc;
    }

    /**
     * @param mixed $agree_coc
     */
    public function setAgreeCoc($agree_coc)
    {
        $this->agree_coc = $agree_coc;
    }
}
