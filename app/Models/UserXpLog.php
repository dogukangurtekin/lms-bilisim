<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class UserXpLog extends Model { use HasFactory; protected $fillable=['user_id','coding_activity_id','xp_delta','reason','awarded_on']; }
