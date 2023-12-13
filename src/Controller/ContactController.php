<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\Contact;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        if ($request->getMethod() === 'POST') {
            $email_dest = $request->get('destinataire_email');
            $user_email = "azizlouhichi81@gmail.com";
            $message = $request->get('user_message');

            if (empty($user_email)) {
                $this->addFlash('error', 'Votre page est cassée');
            } else {
                // Create and persist the contact entity
                $contact = new Contact();
                $contact->setMailDest($email_dest);
                $contact->setMessage($message);
                $contact->setCreatedAt(new \DateTime());
                $em->persist($contact);
                $em->flush();

                // Send email to the destination address
                $this->sendEmail($email_dest, $user_email, $message, $mailer);

                $this->addFlash('success', 'Votre message a été envoyé avec succès');
                return $this->redirectToRoute('app_homepage', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('contact/index.html.twig');
    }

    private function sendEmail(string $emailDest, string $userEmail, string $message, MailerInterface $mailer): void
    {
        try {
            $email = (new Email())
                ->from($userEmail)
                ->to($emailDest)
                ->subject('New Contact Form Submission')
                ->text($message);

            $mailer->send($email);
        } catch (\Exception $e) {
            // Handle the exception (e.g., log the error)
            $this->addFlash('error', 'Failed to send email: ' . $e->getMessage());
        }
    }
}