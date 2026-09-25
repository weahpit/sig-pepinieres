<?php

namespace App\Controller;

use App\Entity\Pepiniere;
use App\Form\PepiniereType;
use App\Repository\PepiniereRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pepinieres')]
class PepiniereWebController extends AbstractController
{
    public function __construct(
        private  PepiniereRepository $repository,
        private  EntityManagerInterface $em,
    ) {
    }

    #[Route('', name: 'pepiniere_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pepiniere/index.html.twig', [
            'pepinieres' => $this->repository->findAll(),
        ]);
    }

    #[Route('/new', name: 'pepiniere_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        if (!$this->getUser()){return $this->redirectToRoute("app_login");}
        $pepiniere = new Pepiniere();
        $form = $this->createForm(PepiniereType::class, $pepiniere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->save($pepiniere);
            $this->addFlash('success', 'Pépinière créée avec succès.');

            return $this->redirectToRoute('pepiniere_index');
        }

        return $this->render('pepiniere/new.html.twig', ['form' => $form]);
    }

    #[Route('/{id}', name: 'pepiniere_show_web', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Pepiniere $pepiniere): Response
    {
        return $this->render('pepiniere/show.html.twig', ['pepiniere' => $pepiniere]);
    }

    #[Route('/{id}/edit', name: 'pepiniere_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Pepiniere $pepiniere): Response
    {
        $form = $this->createForm(PepiniereType::class, $pepiniere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pepiniere->setDateMaj(new \DateTimeImmutable());
            $this->em->flush();
            $this->addFlash('success', 'Pépinière mise à jour.');

            return $this->redirectToRoute('pepiniere_index');
        }

        return $this->render('pepiniere/edit.html.twig', [
            'pepiniere' => $pepiniere,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'pepiniere_delete_web', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Pepiniere $pepiniere): Response
    {
        if ($this->isCsrfTokenValid('delete' . $pepiniere->getId(), $request->request->get('_token'))) {
            $this->repository->remove($pepiniere);
            $this->addFlash('success', 'Pépinière supprimée.');
        }

        return $this->redirectToRoute('pepiniere_index');
    }
}
