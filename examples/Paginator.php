<?php

namespace Application\Controllers;

use Application\Models;
use Hideks\Controller;
use Hideks\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{

    public function indexAction(Request $request, $page = 1)
    {
        $paginator = new Paginator(array(
            'totalItens'   => Models\User::count(),
            'currentPage'  => $page,
            'limitPerPage' => 5
        ));

        /* ################################################################## *
         * #### Create a custom pagination with the getAttributes method #### *
         * ################################################################## */

        var_export($paginator->getAttributes());



        /* ################################################################## *
         * ########################### Desktop ############################## *
         * ################################################################## */

        // Basic desktop pagination
        $pagination = $paginator->desktop('route_pagination');

        // Desktop pagination with aditional params
        $pagination = $paginator->desktop('other_route_pagination', array(
            'params' => array(
                'name' => 'value'
            )
        ));

        // Desktop pagination with alternative route name
        $pagination = $paginator->desktop(array(
            'routes' => array(
                'route',
                'other_route_pagination'
            )
        ));

        // Desktop pagination with all options
        $pagination = $paginator->desktop(array(
            'routes' => array(
                'route',
                'other_route_pagination'
            ),
                ), array(
            'params' => array(
                'name' => 'value'
            )
        ));



        /* ################################################################## *
         * ############################# Mobile ############################# *
         * ################################################################## */

        // Basic mobile pagination
        $pagination = $paginator->mobile('route_pagination');

        // Mobile pagination with aditional params
        $pagination = $paginator->mobile('other_route_pagination', array(
            'params' => array(
                'name' => 'value'
            )
        ));

        // Mobile pagination with alternative route name
        $pagination = $paginator->mobile(array(
            'routes' => array(
                'route',
                'other_route_pagination'
            )
        ));

        // Mobile pagination with all options
        $pagination = $paginator->mobile(array(
            'routes' => array(
                'route',
                'other_route_pagination'
            )
                ), array(
            'params' => array(
                'name' => 'value'
            )
        ));



        /* ################################################################### *
         * ######################### Data pagination ######################### *
         * ################################################################### */

        $options = array(
            'limit'  => $paginator->getLimit(),
            'offset' => $paginator->getOffset()
        );

        $users = array();

        foreach (Models\User::all($options) as $user) {
            $users[] = $user;
        }
        
        $this->view->users = $users;
        
        $this->view->pagination = $pagination;

        return $this->renderTo('users/index.html', new Response());
    }

}