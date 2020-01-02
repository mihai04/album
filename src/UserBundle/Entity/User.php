<?php


namespace UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * @ORM\Entity
 * @ORM\Table(name="`user`")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

//    /**
//     * @ORM\Column(name="first_name", type="string", nullable=false)
//     */
//    protected $firstName;
//
//    /**
//     * @ORM\Column(name="last_name", type="string", nullable=false)
//     */
//    protected $lastName;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

//    /**
//     * @return string $firstName
//     */
//    public function getFirstName()
//    {
//        return $this->firstName;
//    }
//
//    /**
//     * @return string $lastName
//     */
//    public function getLastName()
//    {
//        return $this->lastName;
//    }
}