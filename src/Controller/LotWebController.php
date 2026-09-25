<?php

namespace App\Controller;

use App\Entity\Lot;
use App\Form\LotType;
use App\Repository\LotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/lots')]
class LotWebController extends AbstractController
{
    public function __construct(
        private  LotRepository $repository,
        private  EntityManagerInterface $em,
    ) {
    }

    #[Route('', name: 'lot_index', methods: ['GET'])]
    public function index(): Response
    {
        if (!$this->getUser()){return $this->redirectToRoute("app_login");}
        return $this->render('lot/index.html.twig', [
            'lots' => $this->repository->findAll(),
        ]);
    }

    #[Route('/new', name: 'lot_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $lot = new Lot();
        $form = $this->createForm(LotType::class, $lot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->save($lot);
            $this->addFlash('success', 'Lot créé avec succès.');

            return $this->redirectToRoute('lot_index');
        }

        return $this->render('lot/new.html.twig', ['form' => $form]);
    }

    #[Route('/{id}/edit', name: 'lot_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Lot $lot): Response
    {
        $form = $this->createForm(LotType::class, $lot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Lot mis à jour.');

            return $this->redirectToRoute('lot_index');
        }

        return $this->render('lot/edit.html.twig', ['lot' => $lot, 'form' => $form]);
    }

    #[Route('/{id}/delete', name: 'lot_delete_web', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Lot $lot): Response
    {
        if ($this->isCsrfTokenValid('delete' . $lot->getId(), $request->request->get('_token'))) {
            $this->repository->remove($lot);
            $this->addFlash('success', 'Lot supprimé.');
        }

        return $this->redirectToRoute('lot_index');
    }
}
