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
use App\Entity\Post;

/**
 * @Route("/api")
 */
class PostController
{
    private $limit = 10;

    /**
     * @Route("/posts", name="post_list")
     * @Method({"GET"})
     */
    public function index(Request $request, UrlGeneratorInterface $router, EntityManagerInterface $entityManager)
    {
        $page = $request->query->get('page', 1);

        $queryBuilder = $entityManager->getRepository(Post::class)->findAllQueryBuilder();

        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($this->limit);
        $pagerfanta->setCurrentPage($page);

        $posts = [];
        foreach ($pagerfanta->getCurrentPageResults() as $post) {
            $posts[] = $post->toArray();
        }

        $self = $router->generate('post_list', ['page' => $page]);
        $first = $router->generate('post_list', ['page' => 1]);
        $last = $router->generate('post_list', ['page' => $pagerfanta->getNbPages()]);
        $next = ($pagerfanta->hasNextPage()) ? $router->generate('post_list', ['page' => $pagerfanta->getNextPage()]) : null;
        $prev = ($pagerfanta->hasPreviousPage()) ? $router->generate('post_list', ['page' => $pagerfanta->getPreviousPage()]) : null;

        $response = [
            'data' => [
                'posts' => $posts,
            ],
            'total' => $pagerfanta->getNbResults(),
            'count' => count($posts),
            'self' => $self,
            'first' => $first,
            'last' => $last,
            'next' => $next,
            'prev' => $prev,
        ];

        return new JsonResponse($response);
    }

    /**
     * @Route("/posts/{id}", name="post_show")
     * @Method({"GET"})
     */
    public function show($id, EntityManagerInterface $entityManager)
    {
        $post = $entityManager->getRepository(Post::class)->findOneById($id);

        if (! $post) {
            throw new NotFoundHttpException(
                'Post not found for id ' . $id
            );
        }

        $data = [
            'data' => [
                'post' => $post->toArray(),
            ],
        ];

        return new JsonResponse($data);
    }

    /**
     * @Route("/posts", name="post_store")
     * @Method({"POST"})
     */
    public function store(Request $request, EntityManagerInterface $entityManager)
    {
        $content = json_decode($request->getContent());

        $post = new Post();
        $post->setTitle($content->title);
        $post->setBody($content->body);

        $entityManager->persist($post);
        $entityManager->flush();

        $data = [
            'data' => [
                'post' => $post->toArray(),
            ],
        ];

        return new JsonResponse($data);
    }

    /**
     * @Route("/posts/{id}", name="post_update")
     * @Method({"PATCH"})
     */
    public function update($id, Request $request, EntityManagerInterface $entityManager)
    {
        $content = json_decode($request->getContent());

        $post = $entityManager->getRepository(Post::class)->findOneById($id);

        if (! $post) {
            throw new NotFoundHttpException(
                'Post not found for id ' . $id
            );
        }

        if (! empty($content->title)) {
            $post->setTitle($content->title);
        }
        if (! empty($content->body)) {
            $post->setBody($content->body);
        }

        $entityManager->persist($post);
        $entityManager->flush();

        $data = [
            'data' => [
                'post' => $post->toArray(),
            ],
        ];

        return new JsonResponse($data);
    }

    /**
     * @Route("/posts/{id}", name="post_destroy")
     * @Method({"DELETE"})
     */
    public function destroy($id, Request $request, EntityManagerInterface $entityManager)
    {
        $content = json_decode($request->getContent());

        $post = $entityManager->getRepository(Post::class)->findOneById($id);

        if (! $post) {
            throw new NotFoundHttpException(
                'Post not found for id ' . $id
            );
        }

        $entityManager->remove($post);
        $entityManager->flush();

        $data = [
            'data' => [
                'post' => $post->toArray(),
            ],
        ];

        return new JsonResponse($data);
    }
}
