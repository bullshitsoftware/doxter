<?php

namespace App\Controller;

use App\Security\LoginFormAuthenticator;
use LogicException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends Controller
{
    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils, SessionInterface $session): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        $rememberMe = LoginFormAuthenticator::getLastRememberMe($session);

        return $this->render(
            'security/login.html.twig',
            [
                'last_username' => $lastUsername,
                'error' => $error,
                'remember_me' => $rememberMe,
            ],
        );
    }

    #[Route('/logout', name: 'logout')]
    public function logout(): void
    {
        throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
