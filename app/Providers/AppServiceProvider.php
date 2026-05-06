<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Observers\UserObserver;
use App\Policies\AnnouncementPolicy;
use App\Policies\AttendancePolicy;
use App\Policies\CoursePolicy;
use App\Policies\GradePolicy;
use App\Policies\SchoolClassPolicy;
use App\Policies\StudentPolicy;
use App\Policies\TeacherPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Student::class, StudentPolicy::class);
        Gate::policy(Teacher::class, TeacherPolicy::class);
        Gate::policy(SchoolClass::class, SchoolClassPolicy::class);
        Gate::policy(Course::class, CoursePolicy::class);
        Gate::policy(Grade::class, GradePolicy::class);
        Gate::policy(Attendance::class, AttendancePolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Announcement::class, AnnouncementPolicy::class);
        User::observe(UserObserver::class);
    }
}
