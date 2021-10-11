<?php

namespace App\Controller;

use App\Form\ImportType;
use App\Form\UserSettingsType;
use App\Form\PasswordChangeType;
use App\Service\ImportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends Controller
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

            $this->addFlash(self::FLASH_SUCCESS, 'User settings updated');

            return $this->redirectToRoute('settings');
        }

        $passwordForm = $this->createForm(PasswordChangeType::class);
        $passwordForm->handleRequest($request);
        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $user->setPassword($passwordHasher->hashPassword($user, $passwordForm->get('password')->getData()));
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash(self::FLASH_SUCCESS, 'User password updated');

            return $this->redirectToRoute('settings');
        }

        $importForm = $this->createForm(ImportType::class);
        $importForm->handleRequest($request);
        if ($importForm->isSubmitted() && $importForm->isValid()) {
            $import->import($this->getUser(), $importForm->get('content')->getData());

            $this->addFlash(self::FLASH_SUCCESS, 'Import succeed');

            return $this->redirectToRoute('settings');
        }

        return $this->render('user/settings.html.twig', [
            'settings_form' => $settingsForm->createView(),
            'password_form' => $passwordForm->createView(),
            'import_form' => $importForm->createView(),
        ]);
    }
}
