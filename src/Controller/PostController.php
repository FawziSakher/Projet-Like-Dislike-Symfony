<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/post')]
class PostController extends AbstractController
{
    #[Route('/', name: 'app_post_index', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
       
          // Vérifie si l'utilisateur est connecté
    $user = $this->getUser();
    if (!$user) {
        return $this->redirectToRoute('app_login');
    }
     if ($this->isGranted('ROLE_ADMIN')) {
        $posts = $postRepository->findAll();
    } else {
        // Sinon, on affiche uniquement les posts de cet utilisateur
        $posts = $postRepository->findBy(['author' => $user]);
    }

    return $this->render('post/index.html.twig', [
        'posts' => $posts,
    ]);
   
     
    }

    #[Route('/new', name: 'app_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PostRepository $postRepository): Response
    {
       if ($this->getUser()){
            $post = new Post();
            $form = $this->createForm(PostType::class, $post);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $post->setAuthor($this->getUser());

                $postRepository->save($post, true);

                return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->renderForm('post/new.html.twig', [
                'post' => $post,
                'form' => $form,
            ]);

         }
         return $this->redirectToRoute('app_login');
    }

    #[Route('/{id}', name: 'app_post_show', methods: ['GET'])]
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, PostRepository $postRepository): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $postRepository->save($post, true);

            return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('post/edit.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, PostRepository $postRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $postRepository->remove($post, true);
        }

        return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/post/interactions/{id}', name: 'app_post_interactions')]
    #[IsGranted('ROLE_ADMIN')] // Réservé aux admins
    public function showInteractions(Post $post): Response
    {
        $likers = [];
        foreach ($post->getLikes() as $like) {
            $likers[] = [
                'email' => $like->getAuthor()->getEmail(),
                'username' => $like->getAuthor()->getUserIdentifier(),
                'type' => 'Like',
                
            ];
        }

        $dislikers = [];
        foreach ($post->getDislikes() as $dislike) {
            $dislikers[] = [
                'email' => $dislike->getAuthor()->getEmail(),
                'username' => $dislike->getAuthor()->getUserIdentifier(),
                'type' => 'DisLike',
                
            ];
        }

        $allInteractions = array_merge($likers, $dislikers);

       

        return $this->render('post/interactions.html.twig', [
            'post' => $post,
            'interactions' => $allInteractions,
        ]);
    }
    
}