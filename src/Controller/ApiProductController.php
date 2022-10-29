<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
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




class ApiProductController extends AbstractController
{
    /**
     * @Route("/api/product", name="app_api_product" , methods="GET")
     */
    public function index(ProductRepository $productRepository, NormalizerInterface $normalizer)

    {
        $product = $productRepository->findAll();
        $normalized = $normalizer->normalize($product, null, ['groups' => 'product:read']);
        $json = json_encode($normalized);
        return new Response($json, 200, ['Content-type' => 'application/json']);
    }
    // {
    //     return $this->render('api_product/index.html.twig', [
    //         'controller_name' => 'ApiProductController',
    //     ]);
    // }


    /**
     * @Route("/api/product", name="api_product_add", methods="POST")
     */
    public function add( EntityManagerInterface $entityManager,Request $request, SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $contenu = $request->getContent();
        try {
            $product = $serializer->deserialize($contenu, Product::class, 'json');
            $errors = $validator->validate($product);
            if (count($errors) > 0) {
                return $this->json($errors, 400);
            }
            $entityManager->persist($product);
            $entityManager->flush();
            return $this->json($product, 201, [], [
                'groups' => 'product:read'
            ]);
        } catch (NotEncodableValueException $e) {
            return $this->json([
                'status' => 400,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * @Route("/api/deleteproduct/{id}", name="api_product_delete", methods="DELETE") 
     */
       public function deleteProduct(Product $product, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($product);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }


    /**
    * @Route("/api/updateproduct/{id}", name="api_product_update", methods={"GET","PUT"})
    */    
    public function updateProduct(CategoryRepository $categoryRepository ,Request $request, SerializerInterface $serializer, Product $product, EntityManagerInterface $em): JsonResponse
    {
        $updatedProduct = $serializer->deserialize($request->getContent(), Product::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $product]);
        $category_id = json_decode($request->getContent())->category_id;
        $category = $categoryRepository->find($category_id);
        // dump($category);die;
        $updatedProduct->setName($updatedProduct->getName());
        $updatedProduct->setDescription($updatedProduct->getDescription());
        $updatedProduct->setPrice($updatedProduct->getPrice());
        $updatedProduct->setCategory($category);   
        $em->persist($updatedProduct);
        $em->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
   }


}
