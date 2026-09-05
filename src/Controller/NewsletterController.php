<?php

namespace App\Controller;

use App\Entity\Subscriber;
use App\Repository\SubscriberRepository;
use App\Service\BrevoClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class NewsletterController extends AbstractController
{
    #[Route('/newsletter/inscription', name: 'app_newsletter_subscribe', methods: ['POST'])]
    public function subscribe(
        Request $request,
        SubscriberRepository $subscribers,
        ValidatorInterface $validator,
        BrevoClient $brevo,
    ): Response {
        $submittedToken = (string) $request->request->get('_token');
        if (!$this->isCsrfTokenValid('newsletter', $submittedToken)) {
            $this->addFlash('newsletter_error', 'Session expirée, merci de réessayer.');

            return $this->redirectToRoute('app_home', ['_fragment' => 'contact']);
        }

        // Piège à bots : ce champ est masqué, un humain ne le remplit jamais.
        // S'il est rempli, on renvoie une réponse d'apparence normale sans rien enregistrer ni envoyer.
        if ('' !== trim((string) $request->request->get('website'))) {
            $this->addFlash('newsletter_success', 'Presque ! Ouvrez le lien de confirmation qu\'on vient de vous envoyer par e-mail.');

            return $this->redirectToRoute('app_home', ['_fragment' => 'contact']);
        }

        $email = strtolower(trim((string) $request->request->get('email')));

        $candidate = (new Subscriber())->setEmail($email);
        if (\count($validator->validate($candidate)) > 0) {
            $this->addFlash('newsletter_error', 'Cette adresse e-mail ne semble pas valide.');

            return $this->redirectToRoute('app_home', ['_fragment' => 'contact']);
        }

        $existing = $subscribers->findOneByEmail($email);

        if ($existing && $existing->isConfirmed()) {
            $this->addFlash('newsletter_success', 'Vous êtes déjà inscrit — à dimanche.');

            return $this->redirectToRoute('app_home', ['_fragment' => 'contact']);
        }

        // Nouvel abonné, ou abonné existant jamais confirmé : on (re)génère un
        // token et on (re)tente l'e-mail de confirmation.
        $subscriber = $existing ?? $candidate;
        $subscriber->regenerateToken();
        $subscribers->save($subscriber);

        if ($brevo->isConfigured()) {
            $sent = $brevo->sendConfirmationEmail(
                $subscriber,
                $this->generateUrl('app_newsletter_confirm', ['token' => $subscriber->getToken()], UrlGeneratorInterface::ABSOLUTE_URL),
                $this->generateUrl('app_newsletter_unsubscribe', ['token' => $subscriber->getToken()], UrlGeneratorInterface::ABSOLUTE_URL),
            );

            $this->addFlash(
                $sent ? 'newsletter_success' : 'newsletter_error',
                $sent
                    ? 'Presque ! Ouvrez le lien de confirmation qu\'on vient de vous envoyer par e-mail.'
                    : 'L\'envoi de l\'e-mail a échoué. Réessayez dans un instant.'
            );
        } else {
            // Brevo pas encore branché : on garde l'adresse, sans promettre d'envoi.
            $this->addFlash('newsletter_success', 'Inscription enregistrée. Vous serez prévenu au lancement de la newsletter.');
        }

        return $this->redirectToRoute('app_home', ['_fragment' => 'contact']);
    }

    #[Route('/newsletter/confirmation/{token}', name: 'app_newsletter_confirm', methods: ['GET'], requirements: ['token' => '[a-f0-9]{64}'])]
    public function confirm(
        string $token,
        SubscriberRepository $subscribers,
        BrevoClient $brevo,
    ): Response {
        $subscriber = $subscribers->findOneByToken($token);

        if (!$subscriber) {
            return $this->render('newsletter/confirmed.html.twig', ['state' => 'invalid'], new Response('', Response::HTTP_NOT_FOUND));
        }

        if (!$subscriber->isConfirmed()) {
            $subscriber->setConfirmed(true)->setConfirmedAt(new \DateTimeImmutable());
            $subscribers->save($subscriber);
            $brevo->addConfirmedContact($subscriber);
        }

        return $this->render('newsletter/confirmed.html.twig', ['state' => 'ok']);
    }

    #[Route('/newsletter/desinscription/{token}', name: 'app_newsletter_unsubscribe', methods: ['GET'], requirements: ['token' => '[a-f0-9]{64}'])]
    public function unsubscribe(
        string $token,
        SubscriberRepository $subscribers,
    ): Response {
        $subscriber = $subscribers->findOneByToken($token);

        if ($subscriber) {
            $subscribers->remove($subscriber);
        }

        return $this->render('newsletter/unsubscribed.html.twig');
    }
}
