<?php

declare(strict_types=1);

namespace App\Controller\Settings;

use App\Controller\Controller;
use App\Form\UserSettingsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/settings', name: 'settings')]
class UserController extends Controller
{
    public function __invoke(EntityManagerInterface $entityManager, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUserOrException();
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
}
