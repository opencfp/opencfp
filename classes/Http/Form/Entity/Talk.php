<?php
namespace OpenCFP\Http\Form\Entity;

class Talk
{
    protected $id;
    protected $user_id;
    protected $title;
    protected $description;
    protected $type;
    protected $level;
    protected $category;
    protected $desired;
    protected $slides;
    protected $other;
    protected $sponsor;

    public function createFromArray($data)
    {
        $this->setId($data['id']);
        $this->setUserId($data['user_id']);
        $this->setTitle($data['title']);
        $this->setDescription($data['description']);
        $this->setType($data['type']);
        $this->setLevel($data['level']);
        $this->setCategory($data['category']);
        $this->setDesired($data['desired']);
        $this->setSlides($data['slides']);
        $this->setOther($data['other']);
        $this->setSponsor($data['sponsor']);
    }

    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'user_id' => $this->getUserId(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'type' => $this->getType(),
            'level' => $this->getLevel(),
            'category' => $this->getCategory(),
            'desired' => $this->getDesired(),
            'slides' => $this->getSlides(),
            'other' => $this->getOther(),
            'sponsor' => $this->getSponsor(),
        ];
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param mixed $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param mixed $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return mixed
     */
    public function getDesired()
    {
        return $this->desired;
    }

    /**
     * @param mixed $desired
     */
    public function setDesired($desired)
    {
        $this->desired = $desired;
    }

    /**
     * @return mixed
     */
    public function getSlides()
    {
        return $this->slides;
    }

    /**
     * @param mixed $slides
     */
    public function setSlides($slides)
    {
        $this->slides = $slides;
    }

    /**
     * @return mixed
     */
    public function getOther()
    {
        return $this->other;
    }

    /**
     * @param mixed $other
     */
    public function setOther($other)
    {
        $this->other = $other;
    }

    /**
     * @return mixed
     */
    public function getSponsor()
    {
        return $this->sponsor;
    }

    /**
     * @param mixed $sponsor
     */
    public function setSponsor($sponsor)
    {
        $this->sponsor = $sponsor;
    }
}
