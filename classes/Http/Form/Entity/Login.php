<?php
namespace OpenCFP\Http\Form\Entity;

class Login
{
    protected $email;
    protected $password;

    public function createArray()
    {
        return [
            'email' => $this->getEmail(),
            'password' => $this->getPassword(),
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
}
