<?php

namespace App\Controller\Settings;

use App\Controller\Controller;
use App\Form\PasswordChangeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/settings/password', name: 'settings_password')]
class PasswordChangeController extends Controller
{
    public function __invoke(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        Request $request
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        $form = $this->createForm(PasswordChangeType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($passwordHasher->hashPassword($user, $form->get('password')->getData()));
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash(self::FLASH_SUCCESS, 'User password updated');

            return $this->redirectToRoute('settings_password');
        }

        return $this->render('settings/password.html.twig', ['form' => $form->createView()]);
    }
}
