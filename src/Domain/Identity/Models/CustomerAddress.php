<?php

namespace Src\Domain\Identity\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Src\Application\Identity\DataObjects\AddressData;

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
 *
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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public static function register(AddressData $addressData, Customer $customer): self
    {
        $customerAddress = new self();
        $customerAddress->id = $customerAddress->newUniqueId();
        $customerAddress->customer_id = $customer->id;
        $customerAddress->zip_code = $addressData->zipCode;
        $customerAddress->street = $addressData->street;
        $customerAddress->number = $addressData->number;
        $customerAddress->complement = $addressData->complement;
        $customerAddress->neighborhood = $addressData->neighborhood;
        $customerAddress->city = $addressData->city;
        $customerAddress->state = $addressData->state;
        $customerAddress->country = $addressData->country;
        $customerAddress->is_primary = $addressData->isPrimary;

        return $customerAddress;
    }
}
