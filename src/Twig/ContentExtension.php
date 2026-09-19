<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Convertisseur Markdown léger et sans dépendance, taillé pour le carnet Privaris.
 *
 * Gère : blocs de code ```...``` (avec coloration des prompts $ et commentaires #),
 * code `en ligne`, **gras**, listes « - », et paragraphes. Tout est échappé d'abord :
 * aucune balise HTML de l'auteur n'est interprétée, seul le Markdown reconnu produit du HTML.
 */
class ContentExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('carnet', [$this, 'toHtml'], ['is_safe' => ['html']]),
        ];
    }

    public function toHtml(?string $text): string
    {
        if (null === $text || '' === trim($text)) {
            return '';
        }

        $text = str_replace("\r\n", "\n", $text);

        // 1) Isoler les blocs de code ```...``` pour ne pas les toucher ensuite.
        $parts = preg_split('/```[ \t]*([\w+-]*)\n(.*?)\n?```/s', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        $html = '';

        // preg_split renvoie : [texte, lang, code, texte, lang, code, ...]
        for ($i = 0, $n = count($parts); $i < $n; ++$i) {
            if (0 === $i % 3) {
                $html .= $this->renderProse($parts[$i]);
            } elseif (1 === $i % 3) {
                $lang = $parts[$i];
                $code = $parts[$i + 1] ?? '';
                $html .= 'scores' === $lang ? $this->renderScores($code) : $this->renderCode($code, $lang);
                ++$i; // on a consommé le groupe "code"
            }
        }

        return $html;
    }

    private function renderCode(string $code, string $lang): string
    {
        $lines = explode("\n", $code);
        $out = '';
        foreach ($lines as $line) {
            $escaped = htmlspecialchars($line, \ENT_QUOTES, 'UTF-8');
            $trimmed = ltrim($line);
            if (str_starts_with($trimmed, '$')) {
                $escaped = '<span class="cmd">'.$escaped.'</span>';
            } elseif (str_starts_with($trimmed, '#')) {
                $escaped = '<span class="cmt">'.$escaped.'</span>';
            }
            $out .= $escaped."\n";
        }
        $label = $lang ? htmlspecialchars($lang, \ENT_QUOTES, 'UTF-8') : 'terminal';

        return '<figure class="codeblk"><div class="cap"><span>'.$label.'</span></div><pre>'.rtrim($out, "\n").'</pre></figure>';
    }

    /**
     * Bloc ```scores : lignes « Libellé : valeur » rendues en tableau,
     * pire valeur en rouge, meilleure en vert.
     */
    private function renderScores(string $code): string
    {
        $rows = [];
        foreach (explode("\n", trim($code)) as $line) {
            $line = trim($line);
            if ('' === $line) {
                continue;
            }
            $pos = strrpos($line, ':');
            if (false === $pos) {
                $rows[] = ['label' => $line, 'value' => '', 'num' => null];
                continue;
            }
            $value = trim(substr($line, $pos + 1));
            $clean = str_replace([' ', ','], ['', '.'], $value);
            $rows[] = [
                'label' => trim(substr($line, 0, $pos)),
                'value' => $value,
                'num' => is_numeric($clean) ? (float) $clean : null,
            ];
        }

        $nums = array_filter(array_column($rows, 'num'), static fn ($n): bool => null !== $n);
        $min = [] !== $nums ? min($nums) : null;
        $max = [] !== $nums ? max($nums) : null;

        $out = '<div class="scoreboard">';
        foreach ($rows as $r) {
            $cls = '';
            if (null !== $r['num'] && $r['num'] === $min) {
                $cls = ' bad';
            } elseif (null !== $r['num'] && $r['num'] === $max) {
                $cls = ' good';
            }
            $out .= '<div class="srow"><span class="slabel">'.$this->inline($r['label']).'</span>'
                .'<span class="sval'.$cls.'">'.htmlspecialchars($r['value'], \ENT_QUOTES, 'UTF-8').'</span></div>';
        }

        return $out.'</div>';
    }

    private function renderProse(string $text): string
    {
        $text = trim($text, "\n");
        if ('' === $text) {
            return '';
        }

        $blocks = preg_split('/\n{2,}/', $text);
        $html = '';

        foreach ($blocks as $block) {
            $block = trim($block);
            if ('' === $block) {
                continue;
            }

            // Titre : #, ## ou ### seul sur sa ligne.
            if (!str_contains($block, "\n") && preg_match('/^(#{1,3})\s+(.+)$/', $block, $m)) {
                $tag = \strlen($m[1]) >= 3 ? 'h3' : 'h2';
                $html .= '<'.$tag.'>'.$this->inline($m[2]).'</'.$tag.'>';
                continue;
            }

            $lines = explode("\n", $block);

            // Citation : toutes les lignes commencent par "> ".
            $isQuote = [] !== $lines;
            foreach ($lines as $line) {
                if (!preg_match('/^>\s?/', $line)) {
                    $isQuote = false;
                    break;
                }
            }
            if ($isQuote) {
                $inner = array_map(static fn (string $l): string => (string) preg_replace('/^>\s?/', '', $l), $lines);
                $html .= '<blockquote>'.$this->inline(implode("\n", $inner)).'</blockquote>';
                continue;
            }

            // Liste : toutes les lignes commencent par "- " ou "* ".
            $isList = true;
            foreach ($lines as $line) {
                if (!preg_match('/^[-*]\s+/', trim($line))) {
                    $isList = false;
                    break;
                }
            }

            if ($isList) {
                $html .= '<ul>';
                foreach ($lines as $line) {
                    $item = preg_replace('/^[-*]\s+/', '', trim($line));
                    $html .= '<li>'.$this->inline($item).'</li>';
                }
                $html .= '</ul>';
            } else {
                $html .= '<p>'.$this->inline(implode("\n", $lines)).'</p>';
            }
        }

        return $html;
    }

    private function inline(string $text): string
    {
        $text = htmlspecialchars($text, \ENT_QUOTES, 'UTF-8');
        // **gras**
        $text = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text);
        // `code en ligne`
        $text = preg_replace('/`([^`]+)`/', '<code class="inl">$1</code>', $text);
        // [texte](https://lien)
        $text = preg_replace(
            '/\[([^\]]+)\]\((https?:\/\/[^)\s]+)\)/',
            '<a href="$2" target="_blank" rel="noopener">$1</a>',
            $text
        );

        // sauts de ligne simples
        return nl2br($text, false);
    }
}
