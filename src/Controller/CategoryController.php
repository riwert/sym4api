<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use App\Entity\Category;

/**
 * @Route("/api")
 */
class CategoryController
{
    private $limit = 10;

    /**
     * @Route("/categories", name="category_list")
     * @Method({"GET"})
     */
    public function index(Request $request, UrlGeneratorInterface $router, EntityManagerInterface $entityManager)
    {
        $page = $request->query->get('page', 1);

        $queryBuilder = $entityManager->getRepository(Category::class)->findAllAvailableQueryBuilder();

        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($this->limit);
        $pagerfanta->setCurrentPage($page);

        $categories = [];
        foreach ($pagerfanta->getCurrentPageResults() as $category) {
            $categories[] = $category->toArray(false);
        }

        $self = $router->generate('category_list', ['page' => $page], UrlGeneratorInterface::ABSOLUTE_URL);
        $first = $router->generate('category_list', ['page' => 1], UrlGeneratorInterface::ABSOLUTE_URL);
        $last = $router->generate('category_list', ['page' => $pagerfanta->getNbPages()], UrlGeneratorInterface::ABSOLUTE_URL);
        $next = ($pagerfanta->hasNextPage()) ? $router->generate('category_list', ['page' => $pagerfanta->getNextPage()], UrlGeneratorInterface::ABSOLUTE_URL) : null;
        $prev = ($pagerfanta->hasPreviousPage()) ? $router->generate('category_list', ['page' => $pagerfanta->getPreviousPage()], UrlGeneratorInterface::ABSOLUTE_URL) : null;

        $data = [
            'categories' => $categories,
        ];

        $links = [            
            'first' => $first,
            'last' => $last,
            'next' => $next,
            'prev' => $prev,
        ];

        $meta = [
            'limit' => $this->limit,
            'total' => $pagerfanta->getNbResults(),
            'count' => count($categories),
            'self' => $self,
        ];

        $response = [
            'data' => $data,
            'links' => $links,
            'meta' => $meta,            
        ];

        return new JsonResponse($response);
    }

    /**
     * @Route("/categories/{id}", name="category_show")
     * @Method({"GET"})
     */
    public function show($id, EntityManagerInterface $entityManager)
    {
        $category = $entityManager->getRepository(Category::class)->findOneById($id);

        if (! $category) {
            throw new NotFoundHttpException(
                'Category not found for ID ' . $id
            );
        }

        $response = [
            'data' => [
                'category' => $category->toArray(),
            ],
        ];

        return new JsonResponse($response);
    }

    /**
     * @Route("/categories", name="category_store")
     * @Method({"POST"})
     */
    public function store(Request $request, EntityManagerInterface $entityManager)
    {
        $content = json_decode($request->getContent());

        $category = new Category();
        $category->setName($content->name);
        $category->setDescription($content->description);

        $entityManager->persist($category);
        $entityManager->flush();

        $response = [
            'data' => [
                'category' => $category->toArray(),
            ],
        ];

        return new JsonResponse($response);
    }

    /**
     * @Route("/categories/{id}", name="category_update")
     * @Method({"PUT", "PATCH"})
     */
    public function update($id, Request $request, EntityManagerInterface $entityManager)
    {
        $content = json_decode($request->getContent());

        $category = $entityManager->getRepository(Category::class)->findOneById($id);

        if (! $category) {
            throw new NotFoundHttpException(
                'Category not found for ID ' . $id
            );
        }

        if (! empty($content->name)) {
            $category->setName($content->name);
        }
        if (! empty($content->body)) {
            $category->setDescription($content->description);
        }

        $entityManager->persist($category);
        $entityManager->flush();

        $response = [
            'data' => [
                'category' => $category->toArray(),
            ],
        ];

        return new JsonResponse($response);
    }

    /**
     * @Route("/categories/{id}", name="category_destroy")
     * @Method({"DELETE"})
     */
    public function destroy($id, Request $request, EntityManagerInterface $entityManager)
    {
        $content = json_decode($request->getContent());

        $category = $entityManager->getRepository(Category::class)->findOneById($id);

        if (! $category) {
            throw new NotFoundHttpException(
                'Category not found for ID ' . $id
            );
        }

        $entityManager->remove($category);
        $entityManager->flush();

        $response = [
            'data' => [
                'category' => $category->toArray(),
            ],
        ];

        return new JsonResponse($response);
    }
}
