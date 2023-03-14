<?php

namespace App\Controller;

use App\Entity\CreditCard;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class MainController extends AbstractController
{
    #[Route('/')]
    public function redirector()
    {
        return  $this->render('redirector/main.html.twig');

    }

    #[Route('/main-safe')]
    public function renderMain()
    {
        $resp = new Response(
            $this->renderView('base/main.html.twig')
        );
        $resp->headers->set("X-Frame-Options", "DENY");

        return $resp;
    }

    #[Route('/main-unsafe')]
    public function renderMainUnSafe()
    {
        return $this->render('base/main.html.twig');
    }


    #[Route('/jacker')]
    public function renderJacker(
        Request $request
    ) {
        return $this->render(
            'jacker/main.html.twig',
            [
                'totalPayment' => number_format($request->query->get('totalPayment'))
            ]
        );
    }

    #[Route('/page-jacker')]
    public function jackerPage()
    {
        return  $this->render('dangerous/main.html.twig');
    }

    #[Route('/page-jacker-secured')]
    public function jackerPageSecured()
    {
        return  $this->render('dangerous/secured.html.twig');

    }


    #[Route('/admin')]
    public function renderAdmin(
        ManagerRegistry $doctrine,
        SerializerInterface $serializer
    ) {
        return $this->render(
            'admin/main.html.twig',
            [
                'data' => $serializer->serialize(
                    $doctrine->getManager()->getRepository(CreditCard::class)->findAll(),
                    'json',
                    [
                        'circular_reference_handler' => function ($o) {
                            return $o->getId();
                        }
                    ]
                )
            ]
        );
    }


    #[Route('/jacker-admin')]
    public function renderJackerAdmin()
    {
        return $this->render('jacker/admin.html.twig');
    }

    #[Route('/validpayment')]
    public function renderValidPayment()
    {
        return $this->render('validpayment/main.html.twig');
    }


    #[Route('/jacker-post', methods: ['POST'])]
    public function saveInfo(
        ManagerRegistry $doctrine,
        Request $request,
        SerializerInterface $serializer
    ) {
        $cc = $serializer->deserialize($request->getContent(), CreditCard::class, 'json');

        $em = $doctrine->getManager();
        $em->persist($cc);

        $em->flush();
    }
}
