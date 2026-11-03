<?php

return [
    // Onay metinleri (KVKK aydınlatma, kullanım şartları, çerez politikası) anlamlı biçimde değişince artırılır;
    // herkesten bir sonraki girişte yeniden "okudum, onayladım" istenir.
    'version' => 1,

    // Testlerde kapalıdır (phpunit.xml); canlıda her zaman açık.
    'enforced' => (bool) env('CONSENT_ENFORCED', true),
];
