<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class WelcomeController
{
    /**
     * @Route("/")
     * @Method({"GET"})
     */
    public function index()
    {
        return new Response('Welcome! This is sym4api - Symfony 4 JSON REST API.');
    }
}
