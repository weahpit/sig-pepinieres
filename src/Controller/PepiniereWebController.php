<?php

namespace App\Controller;

use App\Entity\Pepiniere;
use App\Entity\PhotoGeoreferencee;
use App\Form\PepiniereType;
use App\Repository\PepiniereRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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

    /**
     * Ajout manuel d'une photo de la pépinière (upload fichier image).
     */
    #[Route('/{id}/photos', name: 'pepiniere_photo_add', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function addPhoto(Request $request, Pepiniere $pepiniere): Response
    {
        if (!$this->getUser()) { return $this->redirectToRoute('app_login'); }

        if (!$this->isCsrfTokenValid('photo_add' . $pepiniere->getId(), $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton de sécurité invalide.');
            return $this->redirectToRoute('pepiniere_show_web', ['id' => $pepiniere->getId()]);
        }

        /** @var UploadedFile|null $file */
        $file = $request->files->get('photo');
        if (!$file instanceof UploadedFile) {
            $this->addFlash('warning', 'Veuillez sélectionner une image.');
            return $this->redirectToRoute('pepiniere_show_web', ['id' => $pepiniere->getId()]);
        }

        // Validation type MIME + taille (<= 5 Mo)
        $mimesAutorises = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($file->getMimeType(), $mimesAutorises, true)) {
            $this->addFlash('danger', 'Format non supporté (JPEG, PNG, WebP ou GIF uniquement).');
            return $this->redirectToRoute('pepiniere_show_web', ['id' => $pepiniere->getId()]);
        }
        if ($file->getSize() > 5 * 1024 * 1024) {
            $this->addFlash('danger', 'Image trop volumineuse (5 Mo maximum).');
            return $this->redirectToRoute('pepiniere_show_web', ['id' => $pepiniere->getId()]);
        }

        $dossier = $this->getParameter('kernel.project_dir') . '/public/uploads/pepinieres';
        if (!is_dir($dossier)) { @mkdir($dossier, 0775, true); }

        $origine = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        // Nom de fichier sûr (sans slugger externe) : ASCII, minuscules, tirets
        $nomSafe = strtolower((string) preg_replace('/[^A-Za-z0-9]+/', '-', $origine));
        $nomSafe = trim($nomSafe, '-') ?: 'photo';
        $nomFichier = $nomSafe . '-' . uniqid() . '.' . $file->guessExtension();

        try {
            $file->move($dossier, $nomFichier);
        } catch (FileException $e) {
            $this->addFlash('danger', "Échec de l'enregistrement de l'image.");
            return $this->redirectToRoute('pepiniere_show_web', ['id' => $pepiniere->getId()]);
        }

        $photo = new PhotoGeoreferencee();
        $photo->setPepiniere($pepiniere);
        $photo->setUrlPhoto('/uploads/pepinieres/' . $nomFichier);
        $photo->setLegende(trim((string) $request->request->get('legende')) ?: null);
        $photo->setDatePrise(new \DateTimeImmutable());
        // Position = coordonnées de la pépinière si disponibles
        $photo->setLatitude($pepiniere->getLatitude());
        $photo->setLongitude($pepiniere->getLongitude());

        $this->em->persist($photo);
        $this->em->flush();

        $this->addFlash('success', 'Photo ajoutée avec succès.');
        return $this->redirectToRoute('pepiniere_show_web', ['id' => $pepiniere->getId()]);
    }

    /**
     * Suppression d'une photo de la pépinière.
     */
    #[Route('/{id}/photos/{photoId}/delete', name: 'pepiniere_photo_delete', methods: ['POST'], requirements: ['id' => '\d+', 'photoId' => '\d+'])]
    public function deletePhoto(Request $request, Pepiniere $pepiniere, int $photoId): Response
    {
        if (!$this->getUser()) { return $this->redirectToRoute('app_login'); }

        $photo = $this->em->getRepository(PhotoGeoreferencee::class)->find($photoId);
        if ($photo && $photo->getPepiniere() === $pepiniere
            && $this->isCsrfTokenValid('photo_delete' . $photoId, $request->request->get('_token'))) {
            // Suppression du fichier physique
            $chemin = $this->getParameter('kernel.project_dir') . '/public' . $photo->getUrlPhoto();
            if (is_file($chemin)) { @unlink($chemin); }
            $this->em->remove($photo);
            $this->em->flush();
            $this->addFlash('success', 'Photo supprimée.');
        }

        return $this->redirectToRoute('pepiniere_show_web', ['id' => $pepiniere->getId()]);
    }
}
