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
use App\Entity\Tag;
use App\Entity\Post;

/**
 * @Route("/api")
 */
class TagController
{
    private $limit = 10;

    /**
     * @Route("/tags", name="tagry_list")
     * @Method({"GET"})
     */
    public function index(Request $request, UrlGeneratorInterface $router, EntityManagerInterface $entityManager)
    {
        $page = $request->query->get('page', 1);

        $queryBuilder = $entityManager->getRepository(Tag::class)->findAllAvailableQueryBuilder();

        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($this->limit);
        $pagerfanta->setCurrentPage($page);

        $tags = [];
        foreach ($pagerfanta->getCurrentPageResults() as $tag) {
            $tags[] = $tag->toArray(false);
        }

        $self = $router->generate('tagry_list', ['page' => $page], UrlGeneratorInterface::ABSOLUTE_URL);
        $first = $router->generate('tagry_list', ['page' => 1], UrlGeneratorInterface::ABSOLUTE_URL);
        $last = $router->generate('tagry_list', ['page' => $pagerfanta->getNbPages()], UrlGeneratorInterface::ABSOLUTE_URL);
        $next = ($pagerfanta->hasNextPage()) ? $router->generate('tagry_list', ['page' => $pagerfanta->getNextPage()], UrlGeneratorInterface::ABSOLUTE_URL) : null;
        $prev = ($pagerfanta->hasPreviousPage()) ? $router->generate('tagry_list', ['page' => $pagerfanta->getPreviousPage()], UrlGeneratorInterface::ABSOLUTE_URL) : null;

        $data = [
            'tags' => $tags,
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
            'count' => count($tags),
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
     * @Route("/tags/{id}", name="tagry_show")
     * @Method({"GET"})
     */
    public function show($id, EntityManagerInterface $entityManager)
    {
        $tag = $entityManager->getRepository(Tag::class)->findOneById($id);

        if (! $tag) {
            throw new NotFoundHttpException(
                'Tag not found for ID ' . $id
            );
        }

        $response = [
            'data' => [
                'tag' => $tag->toArray(),
            ],
        ];

        return new JsonResponse($response);
    }

    /**
     * @Route("/tags", name="tagy_store")
     * @Method({"POST"})
     */
    public function store(Request $request, EntityManagerInterface $entityManager)
    {
        $content = json_decode($request->getContent());

        $tag = new Tag();
        $tag->setName($content->name);
        if (! empty($content->posts)) {
            foreach ($content->posts as $postId) {
                $post = $entityManager->getRepository(Post::class)->findOneById($postId);

                if (! $post) {
                    throw new NotFoundHttpException(
                        'Post not found for ID ' . $postId
                    );
                }

                $tag->addPost($post);
            }
        }

        $entityManager->persist($tag);
        $entityManager->flush();

        $response = [
            'data' => [
                'tag' => $tag->toArray(),
            ],
        ];

        return new JsonResponse($response);
    }

    /**
     * @Route("/tags/{id}", name="tag_update")
     * @Method({"PUT", "PATCH"})
     */
    public function update($id, Request $request, EntityManagerInterface $entityManager)
    {
        $content = json_decode($request->getContent());

        $tag = $entityManager->getRepository(Tag::class)->findOneById($id);

        if (! $tag) {
            throw new NotFoundHttpException(
                'Tag not found for ID ' . $id
            );
        }

        if (! empty($content->name)) {
            $tag->setName($content->name);
        }
        if (isset($content->posts)) {
            // Remove existing posts
            foreach ($tag->getPosts() as $post) {
                $tag->removePost($post);
            }
        }
        if (! empty($content->posts)) {            
            // Add new posts
            foreach ($content->posts as $postId) {
                $post = $entityManager->getRepository(Post::class)->findOneById($postId);

                if (! $post) {
                    throw new NotFoundHttpException(
                        'Post not found for ID ' . $postId
                    );
                }

                $tag->addPost($post);
            }
        }

        $entityManager->persist($tag);
        $entityManager->flush();

        $response = [
            'data' => [
                'tag' => $tag->toArray(),
            ],
        ];

        return new JsonResponse($response);
    }

    /**
     * @Route("/tags/{id}", name="tagdestroy")
     * @Method({"DELETE"})
     */
    public function destroy($id, Request $request, EntityManagerInterface $entityManager)
    {
        $content = json_decode($request->getContent());

        $tag = $entityManager->getRepository(Tag::class)->findOneById($id);

        if (! $tag) {
            throw new NotFoundHttpException(
                'Tag not found for ID ' . $id
            );
        }

        $entityManager->remove($tag);
        $entityManager->flush();

        $response = [
            'data' => [
                'tag' => $tag->toArray(),
            ],
        ];

        return new JsonResponse($response);
    }
}
