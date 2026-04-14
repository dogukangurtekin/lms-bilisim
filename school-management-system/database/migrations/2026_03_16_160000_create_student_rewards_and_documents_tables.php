<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avatars', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('image_path', 255);
            $table->unsignedInteger('required_xp')->default(0)->index();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('icon', 120)->nullable();
            $table->string('description', 255)->nullable();
            $table->unsignedInteger('xp_threshold')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('student_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->unique()->constrained('students')->cascadeOnDelete();
            $table->string('username', 120)->index();
            $table->string('plain_password', 120);
            $table->timestamps();
        });

        Schema::create('student_avatar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('avatar_id')->constrained('avatars')->cascadeOnDelete();
            $table->timestamp('unlocked_at')->nullable();
            $table->unique(['student_id', 'avatar_id'], 'student_avatar_unique');
        });

        Schema::create('student_badge', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained('badges')->cascadeOnDelete();
            $table->timestamp('awarded_at')->nullable();
            $table->unique(['student_id', 'badge_id'], 'student_badge_unique');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('current_avatar_id')->nullable()->after('school_class_id')->constrained('avatars')->nullOnDelete();
        });

        DB::table('avatars')->insert([
            ['name' => 'Robot', 'image_path' => 'avatars/3d/robot.png', 'required_xp' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ninja', 'image_path' => 'avatars/3d/ninja.png', 'required_xp' => 100, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Astronot', 'image_path' => 'avatars/3d/astronaut.png', 'required_xp' => 250, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Super Kahraman', 'image_path' => 'avatars/3d/man-superhero.png', 'required_xp' => 500, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('badges')->insert([
            ['name' => 'Baslangic Rozeti', 'icon' => 'star', 'description' => '50 XP barajini asti.', 'xp_threshold' => 50, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kod Kahramani', 'icon' => 'code', 'description' => '200 XP barajini asti.', 'xp_threshold' => 200, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Usta Gelistirici', 'icon' => 'rocket', 'description' => '500 XP barajini asti.', 'xp_threshold' => 500, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_avatar_id');
        });
        Schema::dropIfExists('student_badge');
        Schema::dropIfExists('student_avatar');
        Schema::dropIfExists('student_credentials');
        Schema::dropIfExists('badges');
        Schema::dropIfExists('avatars');
    }
};

