<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Address;
use AppBundle\Entity\Restaurant;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{_locale}", requirements={ "_locale": "%locale_regex%" })
 */
class IndexController extends AbstractController
{
    const MAX_RESULTS = 3;

    /**
     * @Route("/", name="homepage")
     * @Template
     */
    public function indexAction()
    {
        $restaurantRepository = $this->getDoctrine()->getRepository(Restaurant::class);

        $restaurants = $restaurantRepository->findRandom(self::MAX_RESULTS);
        $countAll = $restaurantRepository->countAll();

        $showMore = $countAll > count($restaurants);

        $user = $this->getUser();
        if ($user) {
            $addresses = $user->getAddresses()->toArray();
        } else {
            $addresses = [];
        }

        $addressesNormalized = array_map(function ($address) {

            return $this->get('serializer')->normalize($address, 'jsonld', [
                'resource_class' => Address::class,
                'operation_type' => 'item',
                'item_operation_name' => 'get',
                'groups' => ['address', 'place']
            ]);
        }, $addresses);

        return array(
            'restaurants' => $restaurants,
            'max_results' => self::MAX_RESULTS,
            'show_more' => $showMore,
            'addresses_normalized' => $addressesNormalized,
        );
    }
}
