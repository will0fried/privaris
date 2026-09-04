<?php

namespace App\Controller;

use App\Entity\Subscriber;
use App\Repository\SubscriberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class NewsletterController extends AbstractController
{
    #[Route('/newsletter/inscription', name: 'app_newsletter_subscribe', methods: ['POST'])]
    public function subscribe(
        Request $request,
        SubscriberRepository $subscribers,
        ValidatorInterface $validator,
    ): Response {
        $submittedToken = (string) $request->request->get('_token');
        if (!$this->isCsrfTokenValid('newsletter', $submittedToken)) {
            $this->addFlash('newsletter_error', 'Session expirée, merci de réessayer.');

            return $this->redirectToRoute('app_home', ['_fragment' => 'contact']);
        }

        $email = trim((string) $request->request->get('email'));

        $subscriber = (new Subscriber())->setEmail($email);
        $errors = $validator->validate($subscriber);

        if (count($errors) > 0) {
            $this->addFlash('newsletter_error', 'Cette adresse e-mail ne semble pas valide.');

            return $this->redirectToRoute('app_home', ['_fragment' => 'contact']);
        }

        if ($subscribers->existsForEmail($email)) {
            $this->addFlash('newsletter_success', 'Vous êtes déjà inscrit — à dimanche.');

            return $this->redirectToRoute('app_home', ['_fragment' => 'contact']);
        }

        $subscribers->save($subscriber);
        $this->addFlash('newsletter_success', 'Inscription enregistrée. Le prochain relevé arrive dimanche.');

        return $this->redirectToRoute('app_home', ['_fragment' => 'contact']);
    }
}
