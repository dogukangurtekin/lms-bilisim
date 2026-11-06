<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Services\StudentGameAccessService;
use App\Support\LabCatalog;

/** Kod Laboratuvarı sayfası: laboratuvar kartları. Öğrenci yalnızca kendisine açılanları görür. */
class LabController extends Controller
{
    public function __construct(private StudentGameAccessService $gameAccess)
    {
    }

    public function index()
    {
        $user = auth()->user();
        $student = $user?->student;
        $labs = LabCatalog::LABS;
        if ($student instanceof Student) {
            $labs = array_filter($labs, fn ($lab, $slug) => $this->gameAccess->canPlay($student, $slug), ARRAY_FILTER_USE_BOTH);
        }

        return view('labs.index', [
            'labs' => $labs,
            'upcoming' => LabCatalog::UPCOMING,
            'isStudent' => $student instanceof Student,
            'canAssign' => (bool) $user?->hasRole('admin', 'teacher'),
        ]);
    }
}
