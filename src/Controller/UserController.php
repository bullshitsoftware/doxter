<?php

namespace App\Controller;

use App\Form\UserSettingsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
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

            return $this->redirectToRoute('settings');
        }

        return $this->render('user/settings.html.twig', ['form' => $form->createView()]);
    }
}
