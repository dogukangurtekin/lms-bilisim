<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class UserStreak extends Model { use HasFactory; protected $fillable=['user_id','current_streak','best_streak','last_activity_date']; }
