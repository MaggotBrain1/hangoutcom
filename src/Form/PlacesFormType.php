<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Person;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

// Include JSON Response
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Person controller.
 *
 */
class PersonController extends Controller
{
    // Rest of your original controller


    /**
     * Returns a JSON string with the neighborhoods of the City with the providen id.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function listNeighborhoodsOfCityAction(Request $request)
    {
        // Get Entity manager and repository
        $em = $this->getDoctrine()->getManager();
        $neighborhoodsRepository = $em->getRepository("AppBundle:Neighborhood");

        // Search the neighborhoods that belongs to the city with the given id as GET parameter "cityid"
        $neighborhoods = $neighborhoodsRepository->createQueryBuilder("q")
            ->where("q.city = :cityid")
            ->setParameter("cityid", $request->query->get("cityid"))
            ->getQuery()
            ->getResult();

        // Serialize into an array the data that we need, in this case only name and id
        // Note: you can use a serializer as well, for explanation purposes, we'll do it manually
        $responseArray = array();
        foreach($neighborhoods as $neighborhood){
            $responseArray[] = array(
                "id" => $neighborhood->getId(),
                "name" => $neighborhood->getName()
            );
        }

        // Return array with structure of the neighborhoods of the providen city id
        return new JsonResponse($responseArray);

        // e.g
        // [{"id":"3","name":"Treasure Island"},{"id":"4","name":"Presidio of San Francisco"}]
    }
}
