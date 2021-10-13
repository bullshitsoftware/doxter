<?php

namespace App\Controller;

use App\Form\ImportType;
use App\Form\PasswordChangeType;
use App\Form\UserSettingsType;
use App\Service\ImportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class SettingsController extends Controller
{
    #[Route('/settings', name: 'settings')]
    public function settings(EntityManagerInterface $entityManager, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        $form = $this->createForm(UserSettingsType::class, $user->getSettings());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash(self::FLASH_SUCCESS, 'User settings updated');

            return $this->redirectToRoute('settings');
        }

        return $this->render('settings/app.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/settings/password', name: 'settings_password')]
    public function password(
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

    #[Route('/settings/import', name: 'settings_import')]
    public function import(ImportService $import, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $form = $this->createForm(ImportType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $import->import($this->getUser(), $form->get('content')->getData());

            $this->addFlash(self::FLASH_SUCCESS, 'Import succeed');

            return $this->redirectToRoute('settings_import');
        }

        return $this->render('settings/import.html.twig', ['form' => $form->createView()]);
    }
}
