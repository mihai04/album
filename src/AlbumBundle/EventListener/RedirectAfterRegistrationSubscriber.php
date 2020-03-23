<?php


namespace AlbumBundle\EventListener;


use AlbumBundle\Entity\AuthCode;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use AlbumBundle\Entity\User;


class RedirectAfterRegistrationSubscriber implements EventSubscriberInterface
{
    /** @var EntityManagerInterface */
    private $em;

    use TargetPathTrait;
    const ROLE_USER = 'ROLE_USER';

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * RedirectAfterRegistrationSubscriber constructor.
     * @param RouterInterface $router
     * @param EntityManager $em
     */
    public function __construct(RouterInterface $router, EntityManagerInterface $em)
    {
        $this->router = $router;
        $this->em = $em;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * ['eventName' => 'methodName']
     *  * ['eventName' => ['methodName', $priority]]
     *  * ['eventName' => [['methodName1', $priority], ['methodName2']]]
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
          FOSUserEvents::REGISTRATION_SUCCESS => 'onRegistrationSuccess'
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function onRegistrationSuccess(FormEvent $event)
    {
        /** @var User $user  */
        $user = $event->getForm()->getData();

        $user->addRole(self::ROLE_USER);

//        $apiToken = new ApiToken();
//        $this->em->persist();

        $url = $this->getTargetPath($event->getRequest()->getSession(), 'main');

        if (!$url) {
            $url = $this->router->generate('album_homepage');
        }

        $response = new RedirectResponse($url);
        $event->setResponse($response);
    }
}