<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Réception des images déposées/collées dans l'éditeur d'article.
 * Range le fichier dans public/uploads/carnet (même dossier que les images du carnet)
 * et renvoie son lien au format attendu par EasyMDE : {"data":{"filePath":"..."}}.
 */
#[IsGranted('ROLE_ADMIN')]
class CarnetUploadController extends AbstractController
{
    #[Route('/admin/carnet/upload-image', name: 'admin_carnet_upload_image', methods: ['POST'])]
    public function upload(Request $request, SluggerInterface $slugger): JsonResponse
    {
        $file = $request->files->get('image');
        if (!$file) {
            return $this->json(['error' => 'Aucune image reçue.'], Response::HTTP_BAD_REQUEST);
        }

        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($file->getMimeType(), $allowed, true)) {
            return $this->json(['error' => 'Format non supporté (JPEG, PNG, WebP ou GIF).'], Response::HTTP_BAD_REQUEST);
        }
        if ($file->getSize() > 8 * 1024 * 1024) {
            return $this->json(['error' => 'Image trop lourde (8 Mo maximum).'], Response::HTTP_BAD_REQUEST);
        }

        $ext = $file->guessExtension() ?: 'bin';
        $stem = (string) $slugger->slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))->lower();
        $stem = substr($stem, 0, 40) ?: 'image';
        $name = sprintf('%s-%s.%s', $stem, bin2hex(random_bytes(4)), $ext);
        $dir = $this->getParameter('kernel.project_dir').'/public/uploads/carnet';

        try {
            $file->move($dir, $name);
        } catch (\Throwable $e) {
            return $this->json(['error' => "Échec de l'enregistrement de l'image."], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json(['data' => ['filePath' => '/uploads/carnet/'.$name]]);
    }
}
