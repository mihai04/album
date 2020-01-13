<?php


namespace AlbumBundle\EventListener;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    private $em;
    private $router;
    private $passwordEncoder;
    private $csrfTokenManager;

    /**
     * LoginFormAuthenticator constructor.
     * @param $em
     * @param $router
     * @param $passwordEncoder
     * @param $csrfTokenManager
     */
    public function __construct(EntityManagerInterface $em, RouterInterface $router,
                                UserPasswordEncoderInterface $passwordEncoder,
                                CsrfTokenManagerInterface $csrfTokenManager)
    {
        $this->em = $em;
        $this->router = $router;
        $this->passwordEncoder = $passwordEncoder;
        $this->csrfTokenManager = $csrfTokenManager;
    }


    /**
     * Return the URL to the login page.
     *
     * @return string
     */
    protected function getLoginUrl()
    {
        return $this->router->generate('fos_user_security_login');
    }

    /**
     * @param Request $request
     * @return array|mixed|void
     */
    public function getCredentials(Request $request)
    {
        $isLoginSubmit = $request->getPathInfo() == '/login_check' && $request->isMethod('POST');
        if (!$isLoginSubmit) {
            // skip authentication
            return;
        }

        $username = $request->request->get('_username');
        $password = $request->request->get('_password');
        $csrfToken = $request->request->get('_csrf_token');

        if (false === $this->csrfTokenManager->isTokenValid(new CsrfToken('authenticate', $csrfToken))) {
            throw new InvalidCsrfTokenException('Invalid CSRF toke!');
        }

        $request->getSession()->set(Security::LAST_USERNAME, $username);

        return [
            'username' => $username,
            'password' => $password,
        ];
    }

    /**
     * Return a UserInterface object based on the credentials.
     *
     * The *credentials* are the return value from getCredentials()
     *
     * You may throw an AuthenticationException if you wish. If you return
     * null, then a UsernameNotFoundException is thrown for you.
     *
     * @param $credentials
     * @param UserProviderInterface $userProvider
     * @return UserInterface|null
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $username = $credentials['username'];

        return $this->em->getRepository('UserBundle:User')
            ->findOneBy(['email' => $username]);
    }

    /**
     * Returns true if the credentials are valid.
     *
     * If any value other than true is returned, authentication will
     * fail. You may also throw an AuthenticationException if you wish
     * to cause authentication to fail.
     *
     * The *credentials* are the return value from getCredentials()
     *
     * @param $credentials
     * @param UserInterface $user
     * @return bool
     *
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        $password = $credentials['password'];

        if ($this->passwordEncoder->isPasswordValid($user, $password)) {
            return true;
        }

        return false;
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param string $provider
     * @return RedirectResponse|RedirectResponse
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $provider)
    {
        $targetPath = null;

        // if the person who registered accesses a secure page and start() was invoked, this was the
        // URL they were onm, and it is the place where you want to redirect them as well
        $targetPath = $this->getTargetPath($request->getSession(), $provider);

        if (!$targetPath) {
            $targetPath = $this->router->generate('album_homepage');
        }

        return new RedirectResponse($targetPath);
    }
}