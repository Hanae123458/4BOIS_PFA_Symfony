<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Repository\ContactRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class Controller extends AbstractController{

    public function home()
    {
        return $this->render("home.html.twig");
    }

    public function aboutUs()
    {
        return $this->render("aboutUs.html.twig");
    }


    #[Route('/products', name: 'app_products')]
    public function products()
    {
        return $this->render("products.html.twig");
    }


    #[Route('/contactUs', name: 'app_contact')]
    public function contactUs()
    {
        return $this->render("contactUs.html.twig");
    }
    public function ajoutContactConfirm(EntityManagerInterface $entityManager, Request $request)
    {
        $nom = $request->request->get('nom');
        $email = $request->request->get('email');
        $numTel = $request->request->get('numTel');
        $message = $request->request->get('message');
        $user = $this->getUser(); 

       $errors = [];

        if (empty($nom)) {
            $errors[] = "Le nom est requis.";
        }
        if (empty($email)) {
            $errors[] = "L'email est requis.";
        }elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'email n'est pas valide.";
        }
        if (empty($numTel)) {
        $errors[] = "Le numéro de téléphone est requis.";
        } elseif (!preg_match('/^[0-9]{10}$/', $numTel)) {
            $errors[] = "Le numéro de téléphone doit contenir exactement 10 chiffres.";
        }
        if (empty($message)) {
            $errors[] = "Le message est requis.";
        }
        if (!$user) {
            $errors[] = "Vous devez être connecté pour envoyer un message.";
        }

        if (!empty($errors)) {
            return $this->render('erreurContact.html.twig', [
            'errors' => $errors,
            // Conserver les valeurs saisies pour éviter de tout ressaisir
            'old_values' => [
                'nom' => $nom,
                'email' => $email,
                'message' => $message,
                'numTel' => $numTel
            ]
        ]);
        }

        $contact = new Contact();
        $contact->setNom($nom);
        $contact->setEmail($email);
        $contact->setNumTel($numTel);
        $contact->setMessage($message);
        $contact->setUser($user);

        $entityManager->persist($contact);
        $entityManager->flush();

        $this->addFlash('success', 'Message envoyé avec succès!');

        return $this->redirectToRoute('app_contact');
    }

}

