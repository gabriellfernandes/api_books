<?php

namespace App\Controller;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AuthorController extends AbstractController
{
    #[Route('/author', name: 'author_list', methods: ["GET"])]
    public function index(AuthorRepository $authorRepository): JsonResponse
    {
        return $this->json([
            'data' => $authorRepository->findAll()
        ], 200, [], ['groups' => 'author_show']);
    }

    #[Route('/author/{id}', name: 'author_single', methods: ["GET"])]
    public function getById($id, AuthorRepository $authorRepository): JsonResponse
    {
        $author =  $authorRepository->find($id);

        if (is_null($author)) {
            return $this->json([
                'message' => 'not found'
            ], 404);
        }

        return $this->json([
            'data' => $author
        ], 200, [], ['groups' => 'author_show']);
    }

    #[Route('/author', name: 'author_create', methods: ["POST"])]
    public function create(Request $request, AuthorRepository $authorRepository): JsonResponse
    {

        if ($request->headers->get('Content-Type') == 'application/json') {
            $data = $request->toArray();
        } else {
            $data = $request->request->all();
        }

        $author = new Author();
        $author->setName($data['name']);
        $author->setBiography($data['biography']);
        $author->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));
        $author->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));

        $authorRepository->save($author, true);

        return $this->json([
            'message' => 'Author created success!',
            'data' => $author
        ], 201);
    }


    #[Route('/author/{id}', name: 'author_update', methods: ["PATCH", "PUT"])]
    public function update($id, Request $request, AuthorRepository $authorRepository, ManagerRegistry $doctrine): JsonResponse
    {

        if ($request->headers->get('Content-Type') == 'application/json') {
            $data = $request->toArray();
        } else {
            $data = $request->request->all();
        }

        $author =  $authorRepository->find($id);

        if (is_null($author)) {
            return $this->json([
                'message' => 'not found'
            ], 404);
        }

        if (array_key_exists('name', $data)) $author->setName($data['name']);
        if (array_key_exists('biography', $data)) $author->setBiography($data['biography']);

        $author->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));

        $doctrine->getManager()->flush();

        return $this->json([
            'message' => 'Author update success!',
            'data' => $author
        ], 200, [], ['groups' => 'author_show']);
    }

    #[Route('/author/{id}', name: 'author_delete', methods: ["DELETE"])]
    public function delete($id, AuthorRepository $authorRepository): JsonResponse
    {
        $author =  $authorRepository->find($id);


        if (is_null($author)) {
            return $this->json([
                'message' => 'not found'
            ], 404);
        }

        $authorRepository->remove($author, true);

        return $this->json([], 204);
    }
}
