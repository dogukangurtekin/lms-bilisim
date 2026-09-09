<?php

namespace App\Support;

/**
 * Ders/slayt "zengin metin" (rich text) alanları için beyaz liste tabanlı
 * HTML temizleyici. Öğretmenlerin (hatta teorik olarak herhangi bir
 * kimlik doğrulanmış kullanıcının, ders içeriği başka bir yoldan
 * etkilenebiliyorsa) girdiği HTML, hiçbir temizlemeden geçmeden doğrudan
 * `{!! !!}` ile sayfaya basılıyordu - bu, script/onerror/javascript: gibi
 * içeriklerle stored XSS'e (başka kullanıcıların - öğrenci, öğretmen, admin -
 * tarayıcısında kod çalıştırma) açık bırakıyordu.
 *
 * Bu sınıf ek bir composer paketine ihtiyaç duymadan (PHP'nin yerleşik
 * DOMDocument'ı ile) sadece izin verilen birkaç biçimlendirme etiketini
 * (b, i, u, strong, em, br, p, ul, ol, li, a) bırakır; her etiketteki TÜM
 * öznitelikleri siler (yalnızca <a> için href'i, sadece http/https/mailto
 * şemasıyla, ayrıca korur), böylece onclick/onerror gibi olay
 * işleyicileri veya javascript: bağlantıları asla sayfaya çıkmaz.
 */
class RichTextSanitizer
{
    private const ALLOWED_TAGS = [
        'b', 'i', 'u', 'strong', 'em', 'br', 'p', 'span',
        'ul', 'ol', 'li', 'a', 'blockquote', 'code',
    ];

    public static function clean(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        if (! class_exists(\DOMDocument::class)) {
            // DOM eklentisi yoksa güvenli tarafta kal: hiç HTML basma, düz metin göster.
            return e(strip_tags($html));
        }

        $wrapped = '<?xml encoding="utf-8" ?><div>' . $html . '</div>';

        $previous = libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($wrapped, LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $dom->getElementsByTagName('div')->item(0);
        if (! $root) {
            return e(strip_tags($html));
        }

        self::sanitizeNode($dom, $root);

        $result = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $result .= $dom->saveHTML($child);
        }

        return $result;
    }

    private static function sanitizeNode(\DOMDocument $dom, \DOMNode $node): void
    {
        $children = iterator_to_array($node->childNodes);
        foreach ($children as $child) {
            if ($child instanceof \DOMComment) {
                $node->removeChild($child);
                continue;
            }

            if ($child instanceof \DOMText) {
                continue;
            }

            if (! $child instanceof \DOMElement) {
                $node->removeChild($child);
                continue;
            }

            $tag = strtolower($child->tagName);

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                // İzin verilmeyen etiket: içindeki metni/alt elemanları koru,
                // sadece etiketin kendisini kaldır (script/style dahil tüm
                // içeriğiyle birlikte silmek daha güvenli olduğundan
                // script/style özel olarak tamamen atılır).
                if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed'], true)) {
                    $node->removeChild($child);
                    continue;
                }
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }

            // <a> için href'i silmeden önce yakala, doğrula, sonra TÜM
            // öznitelikleri kaldırıp (onclick/onerror/style vb. dahil) sadece
            // güvenli bulunursa href'i geri koy.
            $safeHref = null;
            if ($tag === 'a' && $child->hasAttribute('href')) {
                $safeHref = self::sanitizeHref($child->getAttribute('href'));
            }

            foreach (iterator_to_array($child->attributes ?? []) as $attr) {
                $child->removeAttribute($attr->nodeName);
            }

            if ($tag === 'a') {
                if ($safeHref !== null) {
                    $child->setAttribute('href', $safeHref);
                    $child->setAttribute('rel', 'noopener noreferrer');
                    $child->setAttribute('target', '_blank');
                } else {
                    // href yok veya güvensiz (javascript:, data: vb.) - linki
                    // düz metne indir, tıklanabilir bırakma.
                    while ($child->firstChild) {
                        $node->insertBefore($child->firstChild, $child);
                    }
                    $node->removeChild($child);
                    continue;
                }
            }

            self::sanitizeNode($dom, $child);
        }
    }

    /**
     * href değerini beyaz listedeki şemalarla (http, https, mailto) sınırlar;
     * "javascript:", "data:" gibi tehlikeli şemaları ve şema belirtmeden
     * tarayıcı davranışına bırakılan (bazı tarayıcılarda script çalıştırabilen)
     * girdileri reddeder.
     */
    private static function sanitizeHref(string $href): ?string
    {
        $href = trim($href);
        if ($href === '') {
            return null;
        }

        // Göreli yollar ve sayfa-içi çapalar (#, /, ?) güvenlidir.
        if (preg_match('/^(#|\/|\?)/', $href)) {
            return $href;
        }

        $scheme = strtolower((string) parse_url($href, PHP_URL_SCHEME));
        if (in_array($scheme, ['http', 'https', 'mailto'], true)) {
            return $href;
        }

        return null;
    }
}
