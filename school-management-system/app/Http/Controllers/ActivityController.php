<?php

namespace App\Http\Controllers;

class ActivityController extends Controller
{
    public static function games(): array
    {
        return [
            'block-grid-runner' => ['name' => 'Blok Kodlama', 'image' => 'blok-kodlama.png', 'url' => '/block-grid-runner'],
            'block-3d-runner' => ['name' => '3D Blok Kodlama', 'image' => '3d-blok-kodlama.png', 'url' => '/block-3d-runner'],
            'compute-it-runner' => ['name' => 'Compute It Runner', 'image' => 'compute-it.png', 'url' => '/compute-it-runner'],
            'lightbot-runner' => ['name' => 'Lightbot Runner', 'image' => 'code-robot.png', 'url' => '/lightbot-runner'],
            'line-trace-runner' => ['name' => 'Line Trace Runner', 'image' => 'cizgi-oyunu.png', 'url' => '/line-trace-runner'],
            'silent-teacher-runner' => ['name' => 'Silent Teacher Python', 'image' => 'python.png', 'url' => '/silent-teacher-runner'],
            'keyboard-race' => ['name' => 'Klavye Yarışması', 'image' => 'keyboard-runner.png', 'url' => '/keyboard-race'],
            'block-builder-studio' => ['name' => '3D Grid Tasarım', 'image' => '3d-blok-grid-runner.png', 'url' => '/block-builder-studio'],
        ];
    }

    public function index()
    {
        return view('activities.index', ['games' => self::games()]);
    }
}
