<?php

namespace App\Controller;

use App\Repository\ContactRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('Intercepté automatiquement par Symfony.');
    }

    #[Route(path: '/logout-check', name: 'app_logout_check')]
    public function logoutCheck(
        ContactRepository $contactRepository,
        UrlGeneratorInterface $urlGenerator,
        TokenStorageInterface $tokenStorage
    ): RedirectResponse {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $hasContact = $contactRepository->findOneBy(['user' => $user]);

        if ($hasContact) {
            // Déconnecte manuellement
            $tokenStorage->setToken(null);
            return new RedirectResponse($urlGenerator->generate('app_login'));
        }

        // Redirige vers contactUs avec message flash
        $this->addFlash('error', 'Veuillez remplir le formulaire d\'abord ?');
        return $this->redirectToRoute('contactUs');
    }
}
