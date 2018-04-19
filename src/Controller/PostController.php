<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Post;

class PostController
{
    /**
     * @Route("/posts")
     * @Method({"GET"})
     */
    public function index(EntityManagerInterface $entityManager)
    {
        $posts = $entityManager->getRepository(Post::class)->findAll();

        $posts = array_map(function($post) {
            return $post->toArray();
        }, $posts);

        $data = [
            'data' => [
                'posts' => $posts,
            ],
        ];

        return new JsonResponse($data);
    }

    /**
     * @Route("/posts/{id}")
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
     * @Route("/posts")
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
     * @Route("/posts/{id}")
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
            'data' => $post->toArray(),
        ];

        return new JsonResponse($data);
    }

    /**
     * @Route("/posts/{id}")
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
            'data' => $post->toArray(),
        ];

        return new JsonResponse($data);
    }
}
