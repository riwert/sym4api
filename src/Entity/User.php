<?php

namespace App\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User extends BaseUser implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;


    /**
     * @ORM\Column(type="string", unique=true)
     */
    private $apiKey;

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of apiKey
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Set the value of apiKey
     *
     * @return  self
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function getRoles()
    {
        return array('ROLE_USER');
    }

    public function getPassword()
    {
    }
    public function getSalt()
    {
    }
    public function eraseCredentials()
    {
    }
}
