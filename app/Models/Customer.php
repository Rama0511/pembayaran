<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name', 'phone', 'email', 'due_date', 'is_active',
        'activation_date', 'nik', 'gender', 'address', 'package_type', 'custom_package',
    'photo_front', 'photo_modem', 'pppoe_username', 'odp', 'installation_fee',
    'photo_opm', 'photo_ktp', 'latitude', 'longitude'
    ];

    public function invoices()
    {
        return $this->hasMany(\App\Models\Invoice::class);
    }
}
