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
use App\Entity\Category;
use App\Entity\Tag;

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

        $queryBuilder = $entityManager->getRepository(Post::class)->findAllAvailableQueryBuilder();

        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($this->limit);
        $pagerfanta->setCurrentPage($page);

        $posts = [];
        foreach ($pagerfanta->getCurrentPageResults() as $post) {
            $posts[] = $post->toArray();
        }

        $self = $router->generate('post_list', ['page' => $page], UrlGeneratorInterface::ABSOLUTE_URL);
        $first = $router->generate('post_list', ['page' => 1], UrlGeneratorInterface::ABSOLUTE_URL);
        $last = $router->generate('post_list', ['page' => $pagerfanta->getNbPages()], UrlGeneratorInterface::ABSOLUTE_URL);
        $next = ($pagerfanta->hasNextPage()) ? $router->generate('post_list', ['page' => $pagerfanta->getNextPage()], UrlGeneratorInterface::ABSOLUTE_URL) : null;
        $prev = ($pagerfanta->hasPreviousPage()) ? $router->generate('post_list', ['page' => $pagerfanta->getPreviousPage()], UrlGeneratorInterface::ABSOLUTE_URL) : null;

        $data = [
            'posts' => $posts,
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
            'count' => count($posts),
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
     * @Route("/posts/{id}", name="post_show")
     * @Method({"GET"})
     */
    public function show($id, EntityManagerInterface $entityManager)
    {
        $post = $entityManager->getRepository(Post::class)->findOneById($id);

        if (! $post) {
            throw new NotFoundHttpException(
                'Post not found for ID ' . $id
            );
        }

        $response = [
            'data' => [
                'post' => $post->toArray(),
            ],
        ];

        return new JsonResponse($response);
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

        if (! empty($content->category)) {
            $category = $entityManager->getRepository(Category::class)->findOneById($content->category);

            if (! $category) {
                throw new NotFoundHttpException(
                    'Category not found for ID ' . $content->category
                );
            }

            $post->setCategory($category);
        }

        if (! empty($content->tags)) {
            foreach ($content->tags as $tagId) {
                $tag = $entityManager->getRepository(Tag::class)->findOneById($tagId);

                if (! $tag) {
                    throw new NotFoundHttpException(
                        'Tag not found for ID ' . $tagId
                    );
                }

                $post->addTag($tag);
            }
        }

        $entityManager->persist($post);
        $entityManager->flush();

        $response = [
            'data' => [
                'post' => $post->toArray(),
            ],
        ];

        return new JsonResponse($response);
    }

    /**
     * @Route("/posts/{id}", name="post_update")
     * @Method({"PUT", "PATCH"})     
     */
    public function update($id, Request $request, EntityManagerInterface $entityManager)
    {
        $content = json_decode($request->getContent());

        $post = $entityManager->getRepository(Post::class)->findOneById($id);

        if (! $post) {
            throw new NotFoundHttpException(
                'Post not found for ID ' . $id
            );
        }

        if (! empty($content->title)) {
            $post->setTitle($content->title);
        }
        if (! empty($content->body)) {
            $post->setBody($content->body);
        }
        if (! empty($content->category)) {
            $category = $entityManager->getRepository(Category::class)->findOneById($content->category);

            if (! $category) {
                throw new NotFoundHttpException(
                    'Category not found for ID ' . $content->category
                );
            }

            $post->setCategory($category);
        }
        if (isset($content->tags)) {
            // Remove existing tags
            foreach ($post->getTags() as $tag) {
                $post->removeTag($tag);
            }
        }    
        if (! empty($content->tags)) {
            // Add new tags
            foreach ($content->tags as $tagId) {
                $tag = $entityManager->getRepository(Tag::class)->findOneById($tagId);

                if (! $tag) {
                    throw new NotFoundHttpException(
                        'Tag not found for ID ' . $tagId
                    );
                }

                $post->addTag($tag);
            }
        }

        $entityManager->persist($post);
        $entityManager->flush();

        $response = [
            'data' => [
                'post' => $post->toArray(),
            ],
        ];

        return new JsonResponse($response);
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
                'Post not found for ID ' . $id
            );
        }

        $entityManager->remove($post);
        $entityManager->flush();

        $response = [
            'data' => [
                'post' => $post->toArray(),
            ],
        ];

        return new JsonResponse($response);
    }
}
