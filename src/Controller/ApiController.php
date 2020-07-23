<?php

namespace App\Controller;

use App\Entity\Region;
use Doctrine\ORM\Mapping\Entity;
use App\Repository\RegionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{
    /**
     * @Route("/api/regions/api", name="api_add_region_api",methods={"GET"})
     */
    public function addRegionByApi(SerializerInterface $serializer)
    {
        $regionJson=file_get_contents("https://geo.api.gouv.fr/regions");
        // on  doit deserializer 
         $regionObject=$serializer->deserialize($regionJson,'App\Entity\Region[]','json');
         $entityManager = $this->getDoctrine()->getManager();
      
         foreach($regionObject as $region){   
           $entityManager->persist($region);        
          }       
          $entityManager->flush();      
    
            return new JsonResponse("succes",Response::HTTP_CREATED,[],true);
    }

  /**
     * @Route("/api/regions", name="api_show_region",methods={"GET"})
     */
    public function showregion(SerializerInterface $serializer,RegionRepository $repo)
    { 
      $regionObject=$repo->findAll();
      $regionJson=$serializer->serialize($regionObject,'json',
      ["groups"=>["region:read_all"]
      ]
    );
      return new JsonResponse($regionJson,Response::HTTP_OK,[],true);

    }

    /**
     * @Route("/api/regions", name="api_add_region",methods={"POST"})
     */
    public function addregion(SerializerInterface $serializer,Request $request,ValidatorInterface $validator)
    { 
      $regionJson=$request->getContent();
       $region=$serializer->deserialize($regionJson,Region::class,'json');
       $errors = $validator->validate($region);    
          if (count($errors) > 0) {      
              $errorsString =$serializer->serialize($errors,"json");     
          return new JsonResponse( $errorsString ,Response::HTTP_BAD_REQUEST,[],true);  
             }
       $entityManager = $this->getDoctrine()->getManager();    
      $entityManager->persist($region);      
       $entityManager->flush();      
      return new JsonResponse("succes",Response::HTTP_CREATED,[],true);
       

    }

}
