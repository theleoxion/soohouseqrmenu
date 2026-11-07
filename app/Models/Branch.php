<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'city',
        'district',
        'address',
        'latitude',
        'longitude',
        'phone',
        'whatsapp',
        'email',
        'instagram_url',
        'facebook_url',
        'google_maps_url',
        'cover_image',
        'gallery_images',
        'description',
        'is_active',
        'sort_order',
        'seo_title',
        'seo_description',
        'seo_og_image',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'gallery_images' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'branch_user');
    }

    public function hours(): HasMany
    {
        return $this->hasMany(BranchHour::class)->orderBy('day_of_week');
    }

    public function specialDays(): HasMany
    {
        return $this->hasMany(BranchSpecialDay::class)->orderBy('date');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'branch_product')
            ->using(BranchProduct::class)
            ->withPivot([
                'id',
                'is_available',
                'is_sold_out',
                'custom_price',
                'discount_type',
                'discount_value',
                'discount_starts_at',
                'discount_ends_at',
                'sort_order',
            ])
            ->withTimestamps();
    }

    public function branchProducts(): HasMany
    {
        return $this->hasMany(BranchProduct::class);
    }

    public function diningTables(): HasMany
    {
        return $this->hasMany(DiningTable::class)->orderBy('sort_order')->orderBy('id');
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    /**
     * Calculate whether the branch is currently open based on special days and weekly schedule.
     *
     * @return array{is_open: bool, status_text: string, hours_text: string}
     */
    public function openStatus(?Carbon $now = null): array
    {
        $now = $now ?: Carbon::now('Europe/Istanbul');

        if (! $this->is_active) {
            return [
                'is_open' => false,
                'status_text' => 'Şube Geçici Olarak Kapalı',
                'hours_text' => '-',
            ];
        }

        $todayDate = $now->toDateString();
        $specialDay = $this->specialDays->first(fn (BranchSpecialDay $day) => $day->date->toDateString() === $todayDate);

        if ($specialDay) {
            if ($specialDay->is_closed) {
                return [
                    'is_open' => false,
                    'status_text' => 'Şu An Kapalı'.($specialDay->note ? " ({$specialDay->note})" : ''),
                    'hours_text' => 'Bugün Kapalı',
                ];
            }

            if ($specialDay->open_time && $specialDay->close_time) {
                return $this->evaluateTimeWindow($now, $specialDay->open_time, $specialDay->close_time, $specialDay->note);
            }
        }

        $isoDay = (int) $now->isoWeekday(); // 1=Pazartesi .. 7=Pazar
        $dayHour = $this->hours->firstWhere('day_of_week', $isoDay);

        if (! $dayHour) {
            return [
                'is_open' => false,
                'status_text' => 'Çalışma Saati Belirtilmedi',
                'hours_text' => 'Saat bilgisi girilmedi',
            ];
        }

        if ($dayHour->is_closed) {
            return [
                'is_open' => false,
                'status_text' => 'Şu An Kapalı',
                'hours_text' => 'Bugün Kapalı',
            ];
        }

        return $this->evaluateTimeWindow($now, $dayHour->open_time, $dayHour->close_time);
    }

    /**
     * @return array{is_open: bool, status_text: string, hours_text: string}
     */
    protected function evaluateTimeWindow(Carbon $now, string $openTime, string $closeTime, ?string $note = null): array
    {
        $openFormatted = substr($openTime, 0, 5);
        $closeFormatted = substr($closeTime, 0, 5);
        $currentTime = $now->format('H:i');

        if ($closeFormatted >= $openFormatted) {
            $isOpen = $currentTime >= $openFormatted && $currentTime <= $closeFormatted;
        } else {
            // Gece yarısını aşan kapanış saati (örn: 08:00 - 01:00)
            $isOpen = $currentTime >= $openFormatted || $currentTime <= $closeFormatted;
        }

        $hoursText = "{$openFormatted} - {$closeFormatted}".($note ? " ({$note})" : '');

        return [
            'is_open' => $isOpen,
            'status_text' => $isOpen ? 'Şu An Açık' : 'Şu An Kapalı',
            'hours_text' => $hoursText,
        ];
    }
}
