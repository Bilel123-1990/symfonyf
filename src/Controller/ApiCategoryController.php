<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;




class ApiCategoryController extends AbstractController
{
  
     /**
     * @Route("/api/category", name="app_api_category" , methods="GET")
     */
    public function index(CategoryRepository $categoryRepository, NormalizerInterface $normalizer)

    {
        $category = $categoryRepository->findAll();
        $normalized = $normalizer->normalize($category, null, ['groups' => 'category:read']);
        $json = json_encode($normalized);
        return new Response($json, 200, ['Content-type' => 'application/json']);
    }


      /**
     * @Route("/api/category", name="api_category_add", methods="POST")
     */
    public function add( EntityManagerInterface $entityManager,Request $request, SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $contenu = $request->getContent();
        try {
            $category = $serializer->deserialize($contenu, Category::class, 'json');
            $errors = $validator->validate($category);
            if (count($errors) > 0) {
                return $this->json($errors, 400);
            }
            $entityManager->persist($category);
            $entityManager->flush();
            return $this->json($category, 201, [], [
                'groups' => 'category$category:read'
            ]);
        } catch (NotEncodableValueException $e) {
            return $this->json([
                'status' => 400,
                'message' => $e->getMessage()
            ]);
        }
    }

     /**
     * @Route("/api/deletecategory/{id}", name="api_category_delete", methods="DELETE") 
     */
    public function deleteCategory(Category $category, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($category);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/api/updatecategory/{id}", name="api_category_update", methods={"GET","PUT"})
     */
    public function updateCategory( Request $request, SerializerInterface $serializer, Category $category, EntityManagerInterface $em): JsonResponse
    {
        $updateCategory = $serializer->deserialize($request->getContent(), Category::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $category]); 
        $updateCategory->setName($updateCategory->getName()); 
        $em->persist($updateCategory);
        $em->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }




}
