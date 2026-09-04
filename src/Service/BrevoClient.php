<?php

namespace App\Service;

use App\Entity\Subscriber;
use Psr\Log\LoggerInterface;

/**
 * Petit client Brevo (ex-Sendinblue) en cURL natif — pas de dépendance
 * Composer supplémentaire. Deux usages :
 *   1. envoyer l'e-mail de confirmation (double opt-in) — API transactionnelle
 *   2. ajouter l'abonné confirmé à une liste — API contacts
 *
 * Si la clé API n'est pas configurée, le client reste inerte (isConfigured()
 * renvoie false) : le site continue de fonctionner, on stocke juste en base.
 */
class BrevoClient
{
    private const ENDPOINT = 'https://api.brevo.com/v3';

    public function __construct(
        private readonly string $apiKey,
        private readonly string $listId,
        private readonly string $senderEmail,
        private readonly string $senderName,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function isConfigured(): bool
    {
        return '' !== trim($this->apiKey);
    }

    /**
     * Envoie l'e-mail de confirmation d'inscription (double opt-in).
     */
    public function sendConfirmationEmail(Subscriber $subscriber, string $confirmUrl, string $unsubscribeUrl): bool
    {
        if (!$this->isConfigured()) {
            $this->logger->warning('Brevo non configuré : e-mail de confirmation non envoyé.', ['email' => $subscriber->getEmail()]);

            return false;
        }

        $html = $this->confirmationHtml($confirmUrl, $unsubscribeUrl);
        $text = "Bonjour,\n\n"
            ."Vous avez demandé à recevoir « Le relevé du dimanche » de Privaris.\n"
            ."Confirmez votre inscription en ouvrant ce lien :\n$confirmUrl\n\n"
            ."Si vous n'êtes pas à l'origine de cette demande, ignorez ce message.\n\n"
            ."Se désinscrire : $unsubscribeUrl\n";

        return $this->post('/smtp/email', [
            'sender' => ['name' => $this->senderName, 'email' => $this->senderEmail],
            'to' => [['email' => $subscriber->getEmail()]],
            'subject' => 'Confirmez votre inscription — Privaris',
            'htmlContent' => $html,
            'textContent' => $text,
            'tags' => ['newsletter-doi'],
        ]) !== null;
    }

    /**
     * Ajoute (ou met à jour) le contact dans la liste Brevo, une fois confirmé.
     */
    public function addConfirmedContact(Subscriber $subscriber): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $payload = [
            'email' => $subscriber->getEmail(),
            'updateEnabled' => true,
        ];
        if ('' !== trim($this->listId)) {
            $payload['listIds'] = [(int) $this->listId];
        }

        // 201 (créé) ou 204 (mis à jour) = succès. Un doublon renvoie parfois 400.
        $result = $this->post('/contacts', $payload, [200, 201, 204, 400]);

        return $result !== null;
    }

    /**
     * @param int[] $okCodes codes HTTP considérés comme un succès
     *
     * @return array<mixed>|null réponse décodée, ou null en cas d'échec
     */
    private function post(string $path, array $body, array $okCodes = [200, 201, 202, 204]): ?array
    {
        $ch = curl_init(self::ENDPOINT.$path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($body, \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES),
            CURLOPT_HTTPHEADER => [
                'accept: application/json',
                'content-type: application/json',
                'api-key: '.$this->apiKey,
            ],
            CURLOPT_TIMEOUT => 12,
            CURLOPT_CONNECTTIMEOUT => 6,
        ]);

        $raw = curl_exec($ch);
        $status = (int) curl_getinfo($ch, \CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if (false === $raw) {
            $this->logger->error('Brevo : échec réseau.', ['path' => $path, 'error' => $err]);

            return null;
        }

        if (!\in_array($status, $okCodes, true)) {
            $this->logger->error('Brevo : réponse inattendue.', ['path' => $path, 'status' => $status, 'body' => \is_string($raw) ? substr($raw, 0, 500) : '']);

            return null;
        }

        $decoded = '' === $raw ? [] : json_decode((string) $raw, true);

        return \is_array($decoded) ? $decoded : [];
    }

    private function confirmationHtml(string $confirmUrl, string $unsubscribeUrl): string
    {
        $confirm = htmlspecialchars($confirmUrl, \ENT_QUOTES);
        $unsub = htmlspecialchars($unsubscribeUrl, \ENT_QUOTES);

        return <<<HTML
<!doctype html>
<html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;background:#0A0B0D;color:#EDEDE7;font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#0A0B0D;padding:32px 16px;">
<tr><td align="center">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;background:#101216;border:1px solid #23272F;border-radius:10px;overflow:hidden;">
    <tr><td style="padding:28px 32px 8px;">
      <div style="font-family:Archivo,Impact,sans-serif;font-weight:900;font-size:18px;letter-spacing:.04em;color:#F6A733;">PRIVARIS</div>
    </td></tr>
    <tr><td style="padding:8px 32px 4px;">
      <h1 style="margin:0 0 12px;font-size:20px;line-height:1.3;color:#EDEDE7;font-weight:700;">Confirmez votre inscription</h1>
      <p style="margin:0 0 20px;font-size:14px;line-height:1.65;color:#B7BCC4;">
        Vous recevrez « Le relevé du dimanche » — un décryptage, un lab ou un writeup par semaine, sans bruit. Un dernier clic pour valider&nbsp;:
      </p>
      <table role="presentation" cellpadding="0" cellspacing="0"><tr><td style="border-radius:5px;background:#F6A733;">
        <a href="$confirm" style="display:inline-block;padding:13px 26px;font-size:14px;font-weight:600;color:#0A0B0D;text-decoration:none;letter-spacing:.02em;">Confirmer mon inscription</a>
      </td></tr></table>
      <p style="margin:22px 0 0;font-size:12px;line-height:1.6;color:#61666F;">
        Si le bouton ne fonctionne pas, copiez ce lien&nbsp;:<br>
        <span style="color:#8A9099;word-break:break-all;">$confirm</span>
      </p>
    </td></tr>
    <tr><td style="padding:20px 32px 26px;border-top:1px solid #1B1E24;">
      <p style="margin:0;font-size:11.5px;line-height:1.6;color:#61666F;">
        Vous n'êtes pas à l'origine de cette demande&nbsp;? Ignorez simplement ce message, aucune inscription ne sera prise en compte.
        &nbsp;·&nbsp; <a href="$unsub" style="color:#61666F;text-decoration:underline;">Se désinscrire</a>
      </p>
    </td></tr>
  </table>
  <p style="margin:16px 0 0;font-size:11px;color:#4A4F57;">© Privaris · Besançon</p>
</td></tr>
</table>
</body></html>
HTML;
    }
}
