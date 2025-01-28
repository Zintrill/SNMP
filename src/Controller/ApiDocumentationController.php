<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class ApiDocumentationController extends AbstractController
{
    /**
     * @Route("/api/doc", name="api_doc", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->redirect('/api/doc.json');
    }
}
