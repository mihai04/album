<?php
namespace UserBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * User
 *
 * @ORM\Entity
 * @ORM\Table(name="`user`")
 */
class User extends BaseUser
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;
    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="full_name", type="string", nullable=false)
     */
    private $fullName;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ReviewBundle\Entity\Review", mappedBy="reviewer")
     */
    private $reviews;

    /**
     * The property username is mandatory for FOSUserBundle, and hence,
     * username will be set to the e-mail address.
     *
     * @param $email
     * @return BaseUser|UserInterface
     */
    public function setEmail($email)
    {
        $this->setUsername($email);
        return parent::setEmail($email);
    }
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @return string $fullName
     */
    public function getFullName()
    {
        return $this->fullName;
    }
    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }
    /**
     * @param mixed $fullName
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;
    }

    /**
     * @param $role
     * @return $this|BaseUser|UserInterface
     */
    public function addRole($role)
    {
        $role = strtoupper($role);
        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }
        return $this;
    }
}
