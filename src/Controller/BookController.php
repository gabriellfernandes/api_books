<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{
    #[Route('/books', name: 'books_list', methods: ["GET"])]
    public function getAll(BookRepository $bookRepository): JsonResponse
    {
        return $this->json([
            'data' => $bookRepository->findAll()
        ], 200, [],   ['groups' => 'book_show']);
    }

    #[Route('/books/{id}', name: 'books_single', methods: ["GET"])]
    public function getById($id, BookRepository $bookRepository): JsonResponse
    {
        $book = $bookRepository->find($id);

        if (!$book) {
            return $this->json([
                'message' => 'not found'
            ], 404);
        };

        return $this->json([
            'data' => $book
        ], 200,  [], ['groups' => 'book_show']);
    }


    #[Route('/books/search/{title}', name: 'books_title', methods: ["GET"])]
    public function getByTitle($title, BookRepository $bookRepository): JsonResponse
    {
        $book = $bookRepository->findByTitleField($title);

        if (!$book) {
            return $this->json([
                'message' => 'not found'
            ], 404);
        };

        return $this->json([
            'data' => $book
        ], 200, [],   ['groups' => 'book_show']);
    }

    #[Route('/books', name: 'books_create', methods: ["POST"])]
    public function create(Request $request, BookRepository $bookRepository, AuthorRepository $authorRepository): JsonResponse
    {
        if ($request->headers->get('Content-Type') == 'application/json') {
            $data = $request->toArray();
        } else {
            $data = $request->request->all();
        }

        $author = $authorRepository->find($data["author_id"]);

        if (!$author) {
            return $this->json([
                'message' => 'author not found'
            ], 404);
        };

        $book = new Book();
        $book->setTitle($data['title']);
        $book->setIsbn($data['isbn']);
        $book->setAuthor($author);
        $book->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));
        $book->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));

        $bookRepository->save($book, true);

        return $this->json([
            'message' => 'Book created success',
            'data' =>  $book
        ], 201, [], ['groups' => 'book_show']);
    }

    #[Route('/books/{id}', name: 'books_update', methods: ["PUT", "PATCH"])]
    public function update($id, Request $request, BookRepository $bookRepository, ManagerRegistry $doctrine): JsonResponse
    {

        $book = $bookRepository->find($id);

        if (!$book) {
            return $this->json([
                'message' => 'not found'
            ], 404);
        };

        if ($request->headers->get('Content-Type') == 'application/json') {
            $data = $request->toArray();
        } else {
            $data = $request->request->all();
        }

        if (array_key_exists('title', $data)) $book->setTitle($data['title']);
        if (array_key_exists('isbn', $data)) $book->setTitle($data['isbn']);

        $book->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));

        $doctrine->getManager()->flush();

        return $this->json([
            'message' => 'Book created success',
            'data' =>  $book
        ], 200, [],  ['groups' => 'book_show']);
    }

    #[Route('/books/{id}', name: 'books_delete', methods: ["DELETE"])]
    public function delete($id, BookRepository $bookRepository): JsonResponse
    {
        $book = $bookRepository->find($id);

        if (!$book) {
            return $this->json([
                'message' => 'not found'
            ], 404);
        };

        $bookRepository->remove($book, true);

        return $this->json([], 204);
    }
}
