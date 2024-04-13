<?php 

namespace App\Service;

use App\Repository\BookRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Category;

class Pagination
{
    private $repository;
    private $urlGenerator;
    private $security;
    private $nextUrl;
    private $previousUrl;
    private $min;
    private $max;
    private $pages;
    private $result;

    const NUMBER_RESULTS = 10;

    public function __construct(BookRepository $repository, UrlGeneratorInterface $urlGenerator)
    {
        $this->repository = $repository;
        $this->urlGenerator = $urlGenerator;
        // $this->security = $security;
    }

    public function setPages(?Category $category = null): void
    {
        $queryBuilder = $this->repository->createQueryBuilder('b');
        if ($category) {
            $queryBuilder->leftJoin("b.category", "c")
                         ->andWhere('c.id = :id')
                         ->setParameter('id', $category->getId());
        }

        $numberEntities = $queryBuilder->select('count(b.id)')
                                       ->getQuery()
                                       ->getSingleScalarResult();
        $this->pages = ceil($numberEntities / self::NUMBER_RESULTS);
    }

    public function makeUrls(string $route, int $page): void
    {
        $this->nextUrl = $page + 1 <= $this->pages
            ? $this->urlGenerator->generate($route, ['page' => $page + 1])
            : null;

        $this->previousUrl = $page > 1
            ? $this->urlGenerator->generate($route, ['page' => $page - 1])
            : null;
    }

    public function makeRange(int $page): void
    {
        $this->min = ($page - 1) * self::NUMBER_RESULTS;
        $this->max = $this->min + self::NUMBER_RESULTS;
    }

    public function makePage(string $route, int $page, ?Category $category = null): void
    {
        $this->setPages($category);
        $this->makeUrls($route, $page);
        $this->makeRange($page);

        /** @var UserInterface|null $user */
        $user = $this->security->getUser();
        $this->result = $this->repository->findBooksAndCategory($this->min, $this->max, $user, $category);
    }

    public function getResult(): array
    {
        return $this->result;
    }

    public function getNextUrl(): ?string
    {
        return $this->nextUrl;
    }

    public function getPreviousUrl(): ?string
    {
        return $this->previousUrl;
    }
}
