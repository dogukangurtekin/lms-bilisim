<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$ids=[2,4];
foreach(App\Models\Course::whereIn('id',$ids)->get() as $c){
 $p=(array)$c->lesson_payload; $cover=(string)($p['cover_image']??'');
 $norm=preg_replace('#^https?://[^/]+/[^/]+/public/storage/#i','/storage/',$cover);
 $norm=preg_replace('#^https?://[^/]+/public/storage/#i','/storage/',$norm);
 $path=ltrim(str_replace('/storage/','',$norm),'/');
 $exists=Illuminate\Support\Facades\Storage::disk('public')->exists($path)?'ok':'missing';
 echo "ID {$c->id}\ncover={$cover}\nnorm={$norm}\npath={$path}\nexists={$exists}\n---\n";
}
