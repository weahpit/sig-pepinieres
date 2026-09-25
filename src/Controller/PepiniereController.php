<?php

namespace App\Controller;

use App\Entity\Agent;
use App\Entity\Pepiniere;
use App\Repository\PepiniereRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/pepinieres')]
class PepiniereController extends AbstractController
{
    public function __construct(
        private  PepiniereRepository $repository,
        private  EntityManagerInterface $em,
        private  ValidatorInterface $validator,
    ) {
    }

    #[Route('', name: 'pepiniere_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $data = array_map(
            fn (Pepiniere $p) => $this->serialize($p),
            $this->repository->findAll()
        );

        return $this->json($data);
    }

    #[Route('/{id}', name: 'pepiniere_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $pepiniere = $this->repository->find($id);
        if (!$pepiniere) {
            return $this->json(['error' => 'Pepiniere introuvable'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($this->serialize($pepiniere));
    }

    #[Route('', name: 'pepiniere_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true) ?? [];

        $pepiniere = new Pepiniere();
        $this->hydrate($pepiniere, $payload);

        $errors = $this->validator->validate($pepiniere);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->repository->save($pepiniere);

        return $this->json($this->serialize($pepiniere), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'pepiniere_update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $pepiniere = $this->repository->find($id);
        if (!$pepiniere) {
            return $this->json(['error' => 'Pepiniere introuvable'], Response::HTTP_NOT_FOUND);
        }

        $payload = json_decode($request->getContent(), true) ?? [];
        $this->hydrate($pepiniere, $payload);
        $pepiniere->setDateMaj(new \DateTimeImmutable());

        $errors = $this->validator->validate($pepiniere);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->em->flush();

        return $this->json($this->serialize($pepiniere));
    }

    #[Route('/{id}', name: 'pepiniere_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        $pepiniere = $this->repository->find($id);
        if (!$pepiniere) {
            return $this->json(['error' => 'Pepiniere introuvable'], Response::HTTP_NOT_FOUND);
        }

        $this->repository->remove($pepiniere);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    private function hydrate(Pepiniere $p, array $data): void
    {
        if (isset($data['codePepiniere']))         { $p->setCodePepiniere($data['codePepiniere']); }
        if (isset($data['nomPepiniere']))          { $p->setNomPepiniere($data['nomPepiniere']); }
        if (array_key_exists('region', $data))     { $p->setRegion($data['region']); }
        if (array_key_exists('departement', $data)){ $p->setDepartement($data['departement']); }
        if (array_key_exists('sousPrefecture', $data)) { $p->setSousPrefecture($data['sousPrefecture']); }
        if (array_key_exists('village', $data))    { $p->setVillage($data['village']); }
        if (array_key_exists('latitude', $data))   { $p->setLatitude($data['latitude']); }
        if (array_key_exists('longitude', $data))  { $p->setLongitude($data['longitude']); }
        if (array_key_exists('superficieM2', $data)) { $p->setSuperficieM2($data['superficieM2']); }
        if (array_key_exists('organismeGestionnaire', $data)) { $p->setOrganismeGestionnaire($data['organismeGestionnaire']); }
        if (array_key_exists('capaciteProductionAn', $data)) { $p->setCapaciteProductionAn($data['capaciteProductionAn']); }
        if (!empty($data['dateCreation'])) {
            $p->setDateCreation(new \DateTimeImmutable($data['dateCreation']));
        }
        if (!empty($data['responsableId'])) {
            $agent = $this->em->getRepository(Agent::class)->find($data['responsableId']);
            $p->setResponsable($agent);
        }
    }

    private function serialize(Pepiniere $p): array
    {
        return [
            'id'                    => $p->getId(),
            'codePepiniere'         => $p->getCodePepiniere(),
            'nomPepiniere'          => $p->getNomPepiniere(),
            'region'                => $p->getRegion(),
            'departement'           => $p->getDepartement(),
            'sousPrefecture'        => $p->getSousPrefecture(),
            'village'               => $p->getVillage(),
            'latitude'              => $p->getLatitude(),
            'longitude'             => $p->getLongitude(),
            'superficieM2'          => $p->getSuperficieM2(),
            'organismeGestionnaire' => $p->getOrganismeGestionnaire(),
            'capaciteProductionAn'  => $p->getCapaciteProductionAn(),
            'dateCreation'          => $p->getDateCreation()?->format('Y-m-d'),
            'responsable'           => $p->getResponsable()?->getNom(),
        ];
    }
}
