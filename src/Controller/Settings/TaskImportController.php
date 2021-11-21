<?php

declare(strict_types=1);

namespace App\Controller\Settings;

use App\Controller\Controller;
use App\Form\ImportType;
use App\Service\ImportService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/settings/import', name: 'settings_import')]
class TaskImportController extends Controller
{
    public function __invoke(ImportService $import, Request $request): Response
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
