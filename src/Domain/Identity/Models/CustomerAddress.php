<?php

namespace Src\Domain\Identity\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $customer_id
 * @property string $zip_code
 * @property string $street
 * @property string $number
 * @property string $complement
 * @property string $neighborhood
 * @property string $city
 * @property string $state
 * @property string $country
 * @property bool $is_primary
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Customer $customer
 */
class CustomerAddress extends Model
{
    use HasUuids;

    protected $connection = 'identity';

    protected $casts = [
        'is_primary' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $guarded = ['id', 'customer_id'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
