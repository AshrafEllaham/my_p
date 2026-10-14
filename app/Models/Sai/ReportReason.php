<?php

namespace App\Models\Sai;

use App\Enums\UserReportTypeEnum;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportReason extends Model implements TranslatableContract
{
    use Translatable;

    protected $fillable = ['type'];

    public array $translatedAttributes = ['title'];

    protected function casts(): array
    {
        return ['type' => UserReportTypeEnum::class];
    }

    public function reports(): HasMany
    {
        return $this->hasMany(UserReport::class, 'report_reason_id');
    }
}
