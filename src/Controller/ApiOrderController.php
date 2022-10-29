<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\User;
use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;




class ApiOrderController extends AbstractController
{
    /**
    * @Route("/api/order", name="app_api_order" , methods="GET")
    */
    public function index(OrderRepository $orderRepository, NormalizerInterface $normalizer)

    {
        $order = $orderRepository->findAll();
        $normalized = $normalizer->normalize($order, null, ['groups' => 'order:read']);
        $json = json_encode($normalized);
        return new Response($json, 200, ['Content-type' => 'application/json']);
    }
    // {
    //     return $this->render('api_product/index.html.twig', [
    //         'controller_name' => 'ApiProductController',
    //     ]);
    // }


    /**
     * @Route("/api/order", name="api_order_add", methods="POST")
     */
    public function add( EntityManagerInterface $entityManager,Request $request, SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $contenu = $request->getContent();
        try {
            $order = $serializer->deserialize($contenu, Order::class, 'json');
            $errors = $validator->validate($order);
            if (count($errors) > 0) {
                return $this->json($errors, 400);
            }
            $entityManager->persist($order);
            $entityManager->flush();
            return $this->json($order, 201, [], [
                'groups' => 'order:read'
            ]);
        } catch (NotEncodableValueException $e) {
            return $this->json([
                'status' => 400,
                'message' => $e->getMessage()
            ]);
        }
        
    }


     /**
     * @Route("/api/deleteorder/{id}", name="api_order_delete", methods="DELETE") 
     */
    public function deleteOrder(Order $order, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($order);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
    * @Route("/api/updateorder/{id}", name="api_order_update", methods={"GET","PUT"})
    */    
    public function updateOrder(UserRepository $userRepository ,Request $request, SerializerInterface $serializer, Order $order, EntityManagerInterface $em): JsonResponse
    {
        $updateorder = $serializer->deserialize($request->getContent(), Order::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $order]);
         $user_id= json_decode($request->getContent())->user_id;
         $user = $userRepository->find($user_id);
        //dump($user_id);die;
        $updateorder->setDate($updateorder->getDate());
        $updateorder->setTotal($updateorder->getTotal());
        //$updateorder->setUser($user);   
        $em->persist($updateorder);
        $em->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
   }
    

}
