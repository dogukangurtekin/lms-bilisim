<?php
require 'vendor/autoload.php';
$app=require 'bootstrap/app.php';
$kernel=$app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
foreach(App\Models\Course::select('id','name','lesson_payload')->orderBy('id','desc')->take(5)->get() as $c){$p=(array)$c->lesson_payload; echo $c->id.'|'.$c->name.'|'.($p['cover_image']??'').PHP_EOL;}
