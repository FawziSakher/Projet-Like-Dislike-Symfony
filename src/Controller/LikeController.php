<?php

namespace App\Controller;
use App\Entity\Like;
use App\Repository\LikeRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\DislikeRepository;

class LikeController extends AbstractController
{
    #[Route('/like/post/{id}', name: 'app_like')]
    public function index($id, EntityManagerInterface $entityManager, PostRepository $postRepository, LikeRepository $likeRepository , DislikeRepository $dislikeRepository): Response
    {
   if (!$this->getUser()){

            $this->addFlash('info', "Vous devez connecter pour faire une reaction");

            return  $this->redirectToRoute("app_home_page");
        }

        $postId = $postRepository -> find($id);
        $user =$this ->getUser();
        $existingLike = $likeRepository ->findOneBy(['post'=>$postId , "author"=>$user],[]);
        
        $existingDislike = $dislikeRepository->findOneBy(['post' => $postId, 'author' => $user]);
            if ($existingDislike) {
                $entityManager ->remove($existingDislike);
            }

            
        if($existingLike == null){
            $like = new Like();
            $like -> setAuthor($user);
            $like -> setPost($postId);
            $entityManager ->persist($like);
            $entityManager ->flush();
        }else{
            $entityManager ->remove($existingLike);
            $entityManager ->flush();
        }

        return $this -> redirectToRoute('app_home_page');


       // return $this->render('like/index.html.twig', [
       //     'controller_name' => 'LikeController',
        //]);
    }
}
