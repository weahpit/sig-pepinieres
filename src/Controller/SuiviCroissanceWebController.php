<?php

namespace App\Controller;

use App\Entity\SuiviCroissance;
use App\Form\SuiviCroissanceType;
use App\Repository\SuiviCroissanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/suivi-croissance')]
class SuiviCroissanceWebController extends AbstractController
{
    public function __construct(
        private  SuiviCroissanceRepository $repository,
        private  EntityManagerInterface $em,
    ) {
    }

    #[Route('', name: 'croissance_index', methods: ['GET'])]
    public function index(): Response
    {
        if (!$this->getUser()){return $this->redirectToRoute("app_login");}
        return $this->render('suivi_croissance/index.html.twig', [
            'suivis' => $this->repository->findBy([], ['dateMesure' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'croissance_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $suivi = new SuiviCroissance();
        $form = $this->createForm(SuiviCroissanceType::class, $suivi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->save($suivi);
            $this->addFlash('success', 'Mesure de croissance enregistrée.');

            return $this->redirectToRoute('croissance_index');
        }

        return $this->render('suivi_croissance/new.html.twig', ['form' => $form]);
    }

    #[Route('/{id}/edit', name: 'croissance_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, SuiviCroissance $suivi): Response
    {
        $form = $this->createForm(SuiviCroissanceType::class, $suivi);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Mesure mise à jour.');

            return $this->redirectToRoute('croissance_index');
        }

        return $this->render('suivi_croissance/edit.html.twig', ['suivi' => $suivi, 'form' => $form]);
    }

    #[Route('/{id}/delete', name: 'croissance_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, SuiviCroissance $suivi): Response
    {
        if ($this->isCsrfTokenValid('delete' . $suivi->getId(), $request->request->get('_token'))) {
            $this->repository->remove($suivi);
            $this->addFlash('success', 'Mesure supprimée.');
        }

        return $this->redirectToRoute('croissance_index');
    }
}
