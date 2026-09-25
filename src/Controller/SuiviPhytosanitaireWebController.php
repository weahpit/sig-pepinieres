<?php

namespace App\Controller;

use App\Entity\SuiviPhytosanitaire;
use App\Form\SuiviPhytosanitaireType;
use App\Repository\SuiviPhytosanitaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/suivi-phytosanitaire')]
class SuiviPhytosanitaireWebController extends AbstractController
{
    public function __construct(
        private  SuiviPhytosanitaireRepository $repository,
        private  EntityManagerInterface $em,
    ) {
    }

    #[Route('', name: 'phyto_index', methods: ['GET'])]
    public function index(): Response
    {
        if (!$this->getUser()){return $this->redirectToRoute("app_login");}
        return $this->render('suivi_phytosanitaire/index.html.twig', [
            'suivis' => $this->repository->findBy([], ['dateObservation' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'phyto_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $suivi = new SuiviPhytosanitaire();
        $form = $this->createForm(SuiviPhytosanitaireType::class, $suivi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->save($suivi);
            $this->addFlash('success', 'Observation phytosanitaire enregistrée.');

            return $this->redirectToRoute('phyto_index');
        }

        return $this->render('suivi_phytosanitaire/new.html.twig', ['form' => $form]);
    }

    #[Route('/{id}/edit', name: 'phyto_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, SuiviPhytosanitaire $suivi): Response
    {
        $form = $this->createForm(SuiviPhytosanitaireType::class, $suivi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Observation mise à jour.');

            return $this->redirectToRoute('phyto_index');
        }

        return $this->render('suivi_phytosanitaire/edit.html.twig', ['suivi' => $suivi, 'form' => $form]);
    }

    #[Route('/{id}/delete', name: 'phyto_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, SuiviPhytosanitaire $suivi): Response
    {
        if ($this->isCsrfTokenValid('delete' . $suivi->getId(), $request->request->get('_token'))) {
            $this->repository->remove($suivi);
            $this->addFlash('success', 'Observation supprimée.');
        }

        return $this->redirectToRoute('phyto_index');
    }
}
