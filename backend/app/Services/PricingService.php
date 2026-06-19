<?php

namespace App\Services;

use App\Models\Product;

class PricingService
{
    /**
     * Determine the applicable unit price for a product at a given quantity.
     *
     * Logic:
     * 1. Load active tiers sorted by min_qty ASC.
     * 2. Find the tier where min_qty <= qty AND (max_qty IS NULL OR max_qty >= qty).
     * 3. If no matching tier exists, fall back to product->price.
     *
     * @param  Product $product   Product with priceTiers already loaded (or will be lazy-loaded)
     * @param  int     $quantity
     * @return array{ unit_price: float, subtotal: float, tier_applied: bool }
     */
    public function calculatePrice(Product $product, int $quantity): array
    {
        // Load tiers if not already loaded
        $tiers = $product->relationLoaded('priceTiers')
            ? $product->priceTiers
            : $product->priceTiers()->get();

        $unitPrice   = (float) $product->price; // fallback
        $tierApplied = false;

        foreach ($tiers as $tier) {
            $minOk = $quantity >= $tier->min_qty;
            $maxOk = is_null($tier->max_qty) || $quantity <= $tier->max_qty;

            if ($minOk && $maxOk) {
                $unitPrice   = (float) $tier->unit_price;
                $tierApplied = true;
                break;
            }
        }

        return [
            'unit_price'   => $unitPrice,
            'subtotal'     => $unitPrice * $quantity,
            'tier_applied' => $tierApplied,
        ];
    }

    /**
     * Validate that tier ranges do not overlap.
     *
     * Returns null if valid, or an error message string if invalid.
     *
     * @param  array $tiers  Array of ['min_qty'=>int, 'max_qty'=>int|null, ...]
     * @return string|null
     */
    public function validateTiers(array $tiers): ?string
    {
        if (empty($tiers)) {
            return null; // No tiers is fine
        }

        foreach ($tiers as $i => $tier) {
            $minA = (int) $tier['min_qty'];
            $maxA = isset($tier['max_qty']) && $tier['max_qty'] !== '' && $tier['max_qty'] !== null
                ? (int) $tier['max_qty']
                : null;

            // min must be >= 1
            if ($minA < 1) {
                return "Qty Minimum harus minimal 1 di setiap tier.";
            }

            // max must be > min if set
            if ($maxA !== null && $maxA <= $minA) {
                return "Qty Maksimum harus lebih besar dari Qty Minimum di setiap tier.";
            }

            // Check against other tiers for overlap
            foreach ($tiers as $j => $other) {
                if ($i === $j) continue;

                $minB = (int) $other['min_qty'];
                $maxB = isset($other['max_qty']) && $other['max_qty'] !== '' && $other['max_qty'] !== null
                    ? (int) $other['max_qty']
                    : null;

                // Two ranges overlap if: min_A <= max_B AND min_B <= max_A
                // For infinite max (null), treat as very large number
                $effectiveMaxA = $maxA ?? PHP_INT_MAX;
                $effectiveMaxB = $maxB ?? PHP_INT_MAX;

                if ($minA <= $effectiveMaxB && $minB <= $effectiveMaxA) {
                    return "Rentang qty pada tier bertabrakan (overlap). Pastikan setiap range tidak saling tumpang tindih.";
                }
            }
        }

        return null;
    }
}
