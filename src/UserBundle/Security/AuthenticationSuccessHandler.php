<?php


namespace UserBundle\Security;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
//    /**
//     * @var FlashBagInterface
//     */
//    private $flashBag;

//    /**
//     * AuthenticationSuccessHandler constructor.
//     * @param HttpUtils $httpUtils
//     * @param array $options
//     * @param FlashBagInterface $flashBag
//     */
//    public function __construct(HttpUtils $httpUtils, array $options = [], FlashBagInterface $flashBag)
//    {
//        parent::__construct($httpUtils, $options);
//        $this->flashBag = $flashBag;
//    }


    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $token = $event->getAuthenticationToken();
        $request = $event->getRequest();
        $this->onAuthenticationSuccess($request, $token);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        return new RedirectResponse($referer);
    }

//    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
//    {
//        $this->flashBag->add('success', 'Success!');
//
//        return parent::onAuthenticationSuccess($request, $token);
//    }

}