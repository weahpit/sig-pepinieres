<?php

namespace App\Controller;

use App\Entity\Espece;
use App\Entity\Lot;
use App\Entity\Pepiniere;
use App\Entity\Planche;
use App\Repository\LotRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/lots')]
class LotController extends AbstractController
{
    public function __construct(
        private  LotRepository $repository,
        private  EntityManagerInterface $em,
        private  ValidatorInterface $validator,
    ) {
    }

    #[Route('', name: 'lot_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $data = array_map(fn (Lot $l) => $this->serialize($l), $this->repository->findAll());

        return $this->json($data);
    }

    #[Route('/{id}', name: 'lot_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $lot = $this->repository->find($id);
        if (!$lot) {
            return $this->json(['error' => 'Lot introuvable'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($this->serialize($lot));
    }

    #[Route('', name: 'lot_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true) ?? [];

        $lot = new Lot();
        $this->hydrate($lot, $payload);

        $errors = $this->validator->validate($lot);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->repository->save($lot);

        return $this->json($this->serialize($lot), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'lot_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        $lot = $this->repository->find($id);
        if (!$lot) {
            return $this->json(['error' => 'Lot introuvable'], Response::HTTP_NOT_FOUND);
        }

        $this->repository->remove($lot);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    private function hydrate(Lot $lot, array $data): void
    {
        if (isset($data['numeroLot'])) { $lot->setNumeroLot($data['numeroLot']); }
        if (array_key_exists('nbGrainesSemees', $data)) { $lot->setNbGrainesSemees($data['nbGrainesSemees']); }
        if (array_key_exists('nbPlantsLeves', $data))   { $lot->setNbPlantsLeves($data['nbPlantsLeves']); }
        if (!empty($data['dateSemis']))       { $lot->setDateSemis(new \DateTimeImmutable($data['dateSemis'])); }
        if (!empty($data['dateGermination'])) { $lot->setDateGermination(new \DateTimeImmutable($data['dateGermination'])); }
        if (!empty($data['dateSortie']))      { $lot->setDateSortie(new \DateTimeImmutable($data['dateSortie'])); }
        if (!empty($data['pepiniereId'])) {
            $lot->setPepiniere($this->em->getRepository(Pepiniere::class)->find($data['pepiniereId']));
        }
        if (!empty($data['especeId'])) {
            $lot->setEspece($this->em->getRepository(Espece::class)->find($data['especeId']));
        }
        if (!empty($data['plancheId'])) {
            $lot->setPlanche($this->em->getRepository(Planche::class)->find($data['plancheId']));
        }
    }

    private function serialize(Lot $l): array
    {
        return [
            'id'              => $l->getId(),
            'numeroLot'       => $l->getNumeroLot(),
            'pepiniere'       => $l->getPepiniere()?->getCodePepiniere(),
            'espece'          => $l->getEspece()?->getNomCommun(),
            'planche'         => $l->getPlanche()?->getNumeroPlanche(),
            'dateSemis'       => $l->getDateSemis()?->format('Y-m-d'),
            'nbGrainesSemees' => $l->getNbGrainesSemees(),
            'dateGermination' => $l->getDateGermination()?->format('Y-m-d'),
            'nbPlantsLeves'   => $l->getNbPlantsLeves(),
            'tauxGermination' => $l->getTauxGermination(),
            'dateSortie'      => $l->getDateSortie()?->format('Y-m-d'),
        ];
    }
}
