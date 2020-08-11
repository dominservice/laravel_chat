<?php
namespace Dominservice\LaravelChat\Entities;

/**
 * Class Message
 * @package Dominservice\LaravelChat\Entities
 */
class Message
{
    protected $id;
    protected $sender;
    protected $status;
    protected $self;
    protected $content;
    protected $created;

    public function __construct()
    {

    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $sender
     */
    public function setSender($sender)
    {
        $this->sender = $sender;
    }

    /**
     * @return mixed
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }


    /**
     * @param mixed $self
     */
    public function setSelf($self) {
        $this->self = $self;
    }

    /**
     * @return mixed
     */
    public function getSelf() {
        return $this->self;
    }
} 