<?php

/*
 * Eğitim-öğretim yılı takvimi (karne "Dönem Gelişim Grafiği" için).
 *
 * 1. dönem: Eylül - Ocak, 2. dönem: Şubat - Haziran.
 * Yarıyıl tatili tarihleri MEB takvimine göre yıldan yıla değişir; her yıl
 * .env üzerinden ayarlanabilir (varsayılan: Ocak ayının 19'una denk gelen
 * pazartesiden itibaren 2 hafta).
 */
return [
    // Eğitim yılı başlangıcı (ayın günü; ait olduğu haftanın pazartesisinden başlanır)
    'year_start_month' => 9,
    'year_start_day' => 1,

    // Yıl sonu (Haziran sonu)
    'year_end_month' => 6,
    'year_end_day' => 30,

    // Yarıyıl tatili: bu tarihte/sonrasında gelen ilk pazartesi başlar
    'break_month' => (int) env('ACADEMIC_BREAK_MONTH', 1),
    'break_day' => (int) env('ACADEMIC_BREAK_DAY', 19),
    'break_weeks' => (int) env('ACADEMIC_BREAK_WEEKS', 2),
];
