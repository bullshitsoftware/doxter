<?php

namespace App\Controller;

use App\Form\ImportType;
use App\Form\UserSettingsType;
use App\From\PasswordChangeType;
use App\Service\ImportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/settings', name: 'settings')]
    public function settings(
        EntityManagerInterface $entityManager, 
        UserPasswordHasherInterface $passwordHasher, 
        ImportService $import, 
        Request $request
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        $settingsForm = $this->createForm(UserSettingsType::class, $user->getSettings());
        $settingsForm->handleRequest($request);
        if ($settingsForm->isSubmitted() && $settingsForm->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('settings');
        }

        $passwordForm = $this->createForm(PasswordChangeType::class);
        $passwordForm->handleRequest($request);
        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $user->setPassword($passwordHasher->hashPassword($user, $passwordForm->get('password')->getData()));
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('settings');
        }

        $importForm = $this->createForm(ImportType::class);
        $importForm->handleRequest($request);
        if ($importForm->isSubmitted() && $importForm->isValid()) {
            $import->import($this->getUser(), $importForm->get('content')->getData());

            return $this->redirectToRoute('settings');
        }

        return $this->render('user/settings.html.twig', [
            'settings_form' => $settingsForm->createView(),
            'password_form' => $passwordForm->createView(),
            'import_form' => $importForm->createView(),
        ]);
    }
}
