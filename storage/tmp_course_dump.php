<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
foreach (App\Models\Course::select('id','name','lesson_payload','updated_at')->orderBy('id')->get() as $c) {
  $raw = $c->getRawOriginal('lesson_payload');
  $payload = $c->lesson_payload;
  $cover = is_array($payload) ? ($payload['cover_image'] ?? '') : '';
  echo "ID={$c->id} | {$c->name} | cover={$cover} | updated={$c->updated_at}\n";
  echo "RAW=" . substr((string)$raw,0,240) . "\n---\n";
}
