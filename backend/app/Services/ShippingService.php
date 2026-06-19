<?php

namespace App\Services;

use App\Models\Tenant;

class ShippingService
{
    /**
     * Calculate shipping cost based on tenant's shipping mode.
     *
     * @param  Tenant       $tenant
     * @param  string       $orderType      'delivery' | 'pickup'
     * @param  float|null   $customerLat    Customer latitude
     * @param  float|null   $customerLng    Customer longitude
     * @param  string|null  $zoneName       Zone name selected by customer (for zone mode)
     * @return array{cost: float, distance_km: float|null, zone: string|null, mode: string}
     */
    public function calculate(
        Tenant $tenant,
        string $orderType,
        ?float $customerLat = null,
        ?float $customerLng = null,
        ?string $zoneName = null
    ): array {
        // Pickup orders always have no shipping cost
        if ($orderType === 'pickup') {
            return $this->result('none', 0.0);
        }

        $setting = $tenant->catalogSetting;

        if (!$setting) {
            return $this->result('none', 0.0);
        }

        $mode = $setting->shipping_mode ?? 'none';

        return match ($mode) {
            'distance' => $this->calculateByDistance($setting, $customerLat, $customerLng),
            'zone'     => $this->calculateByZone($setting, $zoneName),
            'api'      => $this->result('api', 0.0), // Placeholder: future Biteship/RajaOngkir
            default    => $this->result('none', 0.0),
        };
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Mode DISTANCE: Calculate shipping cost by actual distance.
     */
    private function calculateByDistance(
        \App\Models\TenantCatalogSetting $setting,
        ?float $customerLat,
        ?float $customerLng
    ): array {
        $storeLat = $setting->store_latitude;
        $storeLng = $setting->store_longitude;
        $brackets = $setting->shipping_distances; // [{max_km, cost}] sorted ascending

        if (!$storeLat || !$storeLng || !$customerLat || !$customerLng || empty($brackets)) {
            return $this->result('distance', 0.0);
        }

        $distanceKm = $this->haversineDistanceKm($storeLat, $storeLng, $customerLat, $customerLng);

        // Sort brackets ascending by max_km to ensure correct matching
        usort($brackets, fn($a, $b) => $a['max_km'] <=> $b['max_km']);

        $cost = 0.0;
        foreach ($brackets as $bracket) {
            $maxKm = (float) ($bracket['max_km'] ?? 0);
            $rate  = (float) ($bracket['cost'] ?? 0);

            if ($distanceKm <= $maxKm) {
                $cost = $rate;
                break;
            }

            // If beyond the last bracket, use the last bracket's cost
            $cost = $rate;
        }

        return [
            'mode'        => 'distance',
            'cost'        => $cost,
            'distance_km' => round($distanceKm, 2),
            'zone'        => null,
        ];
    }

    /**
     * Mode ZONE: Calculate shipping cost by selected zone name.
     */
    private function calculateByZone(
        \App\Models\TenantCatalogSetting $setting,
        ?string $zoneName
    ): array {
        $zones = $setting->shipping_zones; // [{name, cost}]

        if (empty($zones) || !$zoneName) {
            return $this->result('zone', 0.0);
        }

        foreach ($zones as $zone) {
            if (strtolower(trim($zone['name'] ?? '')) === strtolower(trim($zoneName))) {
                return [
                    'mode'        => 'zone',
                    'cost'        => (float) ($zone['cost'] ?? 0),
                    'distance_km' => null,
                    'zone'        => $zone['name'],
                ];
            }
        }

        // Zone not found — return 0
        return $this->result('zone', 0.0);
    }

    /**
     * Build a result array with default nullable fields.
     */
    private function result(string $mode, float $cost): array
    {
        return [
            'mode'        => $mode,
            'cost'        => $cost,
            'distance_km' => null,
            'zone'        => null,
        ];
    }

    /**
     * Haversine formula to calculate great-circle distance between two coordinates.
     * Returns distance in kilometers.
     *
     * @param  float $lat1  Store latitude
     * @param  float $lng1  Store longitude
     * @param  float $lat2  Customer latitude
     * @param  float $lng2  Customer longitude
     * @return float  Distance in km
     */
    public function haversineDistanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371.0;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }
}
