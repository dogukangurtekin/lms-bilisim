<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class StudentCredential extends Model
{
    protected $fillable = ['student_id', 'username', 'plain_password'];
    protected $hidden = ['plain_password'];

    protected function plainPassword(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (! is_string($value) || $value === '') {
                    return $value;
                }

                try {
                    return Crypt::decryptString($value);
                } catch (\Throwable) {
                    return $value;
                }
            },
            set: function ($value) {
                $value = is_string($value) ? trim($value) : '';

                return $value === '' ? null : Crypt::encryptString($value);
            },
        );
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
