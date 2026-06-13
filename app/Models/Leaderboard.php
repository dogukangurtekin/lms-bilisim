<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Leaderboard extends Model { use HasFactory; protected $fillable=['coding_activity_id','user_id','score','duration_seconds','rank_no']; }
