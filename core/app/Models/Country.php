<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\GlobalStatus;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
    // Este modelo representa un país en la base de datos.
    // Contiene varias relaciones y métodos para interactuar con otros modelos.
{
    use  Searchable, GlobalStatus;

    public function countryServices()
    // Este método define una relación de uno a muchos con el modelo Service.
    // Un país puede tener múltiples servicios.
    {
        return $this->hasMany(Service::class);
    }

    public function conversionRates()
    // Este método define una relación de uno a muchos con el modelo CurrencyConversionRate.
    // Un país puede tener múltiples tasas de conversión.
    {
        return $this->hasMany(CurrencyConversionRate::class, 'from_country', 'id');
    }

    public function services()
    {
        return $this->hasManyThrough(Service::class, CountryDeliveryMethod::class);
    }

    public function sendingTransfers()
    // Este método define una relación de uno a muchos con el modelo SendMoney.
    // Un país puede ser el país de origen de múltiples transferencias.
    {
        return $this->hasMany(SendMoney::class, 'sending_country_id');
    }

    public function receivingTransfers()
    // Este método define una relación de uno a muchos con el modelo SendMoney.
    // Un país puede ser el país destinatario de múltiples transferencias.
    {
        return $this->hasMany(SendMoney::class, 'recipient_country_id');
    }

    public function deliveryCharges()
    {
        return $this->hasMany(DeliveryCharge::class);
    }

    public function deliveryMethods()
    {
        return $this->belongsToMany(DeliveryMethod::class, 'country_delivery_method', 'country_id', 'delivery_method_id');
    }

    public function countryDeliveryMethods()
    {
        return $this->hasMany(CountryDeliveryMethod::class);
    }

    public function agentStatus(): Attribute
    {
        return new Attribute(
            function () {
                if ($this->has_agent) {
                    $class = 'success';
                    $text = 'Yes';
                } else {
                    $class = 'danger';
                    $text = 'No';
                }

                $html = '<span class="badge badge--' . $class . '">' . trans($text) . '</span>';

                return $html;
            }
        );
    }

    public function scopeSending($query)
    {
        $query->where('is_sending', Status::YES);
    }

    public function scopeReceiving($query)
    {
        $query->where('is_receiving', Status::YES);
    }

    public function scopeHasAgent($query)
    {
        $query->where('has_agent', Status::YES);
    }

    public function scopeReceivableCountries($query)
    {
        $query->active()->receiving()
            ->with([
                'countryDeliveryMethods.deliveryMethod' => function ($query) {
                    $query->select('id', 'name')->active();
                },
                'countryDeliveryMethods.charge:country_delivery_method_id,fixed_charge,percent_charge'
            ])
            ->where(function ($query) {
                $query->whereHas('countryDeliveryMethods.deliveryMethod', function ($deliveryMethod) {
                    $deliveryMethod->active();
                })
                    ->orWhere(function ($query) {
                        if (gs()->agent_module) {
                            $query->hasAgent();
                        }
                    });
            });
    }
}
