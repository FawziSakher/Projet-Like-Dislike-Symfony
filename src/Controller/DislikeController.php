<?php

namespace App\Controller;
use App\Entity\Dislike;
use App\Repository\DislikeRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\LikeRepository;

class DislikeController extends AbstractController
{
    #[Route('/dislike/post/{id}', name: 'app_dislike')]
    public function index($id, EntityManagerInterface $entityManager, PostRepository $postRepository, DislikeRepository $dislikeRepository, LikeRepository $likeRepository): Response
    { 
           if (!$this->getUser()){

            $this->addFlash('info', "Vous devez connecter pour faire une reaction");

            return  $this->redirectToRoute("app_home_page");
        }

        $postId = $postRepository -> find($id);
        $user =$this ->getUser();

        $existingDislike = $dislikeRepository ->findOneBy(['post'=>$postId , "author"=>$user],[]);
        
         $existingLike = $likeRepository->findOneBy(['post' => $postId, 'author' => $user]);
            if ($existingLike) {
               $entityManager ->remove($existingLike);
            }

        if($existingDislike == null){
            $dislike = new Dislike();
            $dislike -> setAuthor($user);
            $dislike -> setPost($postId);
            $entityManager ->persist($dislike);
            $entityManager ->flush();
        }else{
            $entityManager ->remove($existingDislike);
            $entityManager ->flush();
        }

        return $this -> redirectToRoute('app_home_page');
        //return $this->render('dislike/index.html.twig', [
         //   'controller_name' => 'DislikeController',
        //]);
    }
}
