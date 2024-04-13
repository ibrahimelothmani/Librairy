<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Book;
use App\Entity\User;
use App\Form\BookType;
use App\Form\BorrowType;
use App\Form\SortBookType;
use App\Form\SearchBookType;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;

#[IsGranted('ROLE_LIBRAIRIAN')]
class LibrairianController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    #[Route('/librairian', methods: ['GET'])]
    #[Route('/librairian/{page}', name: 'librairian', requirements: ['page' => '\d+'])]
    public function index(Request $request, Pagination $pagination, int $page = 1): Response
    {
        $pagination->makePage($request->get("_route"), $page);

        $form = $this->createForm(SortBookType::class);
        return $this->render('librairian/index.html.twig', [
            'books' => $pagination->getResult(),
            'form' => $form->createView(),
            "nextUrl" => $pagination->getNextUrl(),
            "previousUrl" => $pagination->getPreviousUrl()
        ]);
    }

    #[Route('/librairian/sort/{page}', name: 'librairian_sort', requirements: ['page' => '\d+'])]
    public function bookSort(Request $request, SessionInterface $session, Pagination $pagination, int $page = 1): Response
    {
        $form = $this->createForm(SortBookType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData()["category"];
            $session->set('category', $category);
        }
        $pagination->makePage($request->get("_route"), $page, $session->get("category"));

        return $this->render('librairian/index.html.twig', [
            'books' => $pagination->getResult(),
            'form' => $form->createView(),
            "nextUrl" => $pagination->getNextUrl(),
            "previousUrl" => $pagination->getPreviousUrl()
        ]);
    }

    #[Route('/librairian/book/{id}', name: 'librairian_book')]
    public function singleBook(int $id, Request $request): Response
    {
        $book = $this->entityManager->getRepository(Book::class)->findBookAndUser($id);
        if (!$book) {
            throw $this->createNotFoundException("Ce livre n'existe pas");
        }
        $form = $this->createForm(BorrowType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $user = $this->entityManager->getRepository(User::class)->findOneBy(["code" => $data["code"]]);
            if(!$user) {
                $this->addFlash("danger", "Ce code utilisateur n'est pas valide");
            } else {
                $book->setBorrower($user);
                $this->entityManager->persist($book);
                $this->entityManager->flush();
                $this->addFlash("success", "Le livre a été emprunté");
            }
        }

        return $this->render('librairian/singleBook.html.twig', [
            'book' => $book,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/librairian/book/back/{id}', name: 'librairian_book_back')]
    public function bookBack(Book $book): Response
    {
        if ($book->getAvailability()) {
            $this->addFlash("danger", "Ce livre n'a jamais été emprunté");
        } else {
            $book->setBorrower(null);
            $this->entityManager->persist($book);
            $this->entityManager->flush();
            $this->addFlash("success", "Le livre a été rendu");
        }
        return $this->redirectToRoute('librairian_book', ['id' => $book->getId()]);
    }

    #[Route('/librairian/users', name: 'librairian_users')]
    public function users(): Response
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();
        return $this->render('librairian/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/librairian/user/{id}', name: 'librairian_user')]
    public function singleUser(int $id): Response
    {
        $user = $this->entityManager->getRepository(User::class)->findUserAndBooks($id);
        if (!$user) {
            throw $this->createNotFoundException("Cet utilisateur n'existe pas");
        }
        return $this->render('librairian/singleUser.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/librairian/new/book', name: 'librairian_new_book')]
    public function newBook(Request $request): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $book = $form->getData();
            $this->entityManager->persist($book);
            $this->entityManager->flush();
        }
        return $this->render('librairian/newBook.html.twig', [
            "form" => $form->createView()
        ]);
    }

    #[Route('/librairian/search', name: 'librairian_search')]
    public function searchBook(Request $request): Response
    {
        $books = null;
        $form = $this->createForm(SearchBookType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bookTitle = $form->getData()["title"];
            $books = $this->entityManager->getRepository(Book::class)->findBy(["title" => $bookTitle]);
        }
        return $this->render('librairian/search.html.twig', [
            'books' => $books,
            'form' => $form->createView(),
        ]);
    }
}
