<?php

namespace App\Jobs;

use App\Models\Property;
use App\Models\SimilarProperty;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CalculateSimilarPropertiesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $propertyId
    ) {}

    public function handle(): void
    {
        try {
            $property = Property::with('amenities')->findOrFail($this->propertyId);

            // Find similar properties based on various factors
            $candidates = Property::where('id', '!=', $this->propertyId)
                ->where('status', 'active')
                ->where('city', $property->city) // Same city
                ->get();

            foreach ($candidates as $candidate) {
                $similarityScore = $this->calculateSimilarity($property, $candidate);

                if ($similarityScore >= 50) { // Minimum 50% similarity
                    SimilarProperty::updateOrCreate(
                        [
                            'property_id' => $this->propertyId,
                            'similar_property_id' => $candidate->id,
                        ],
                        [
                            'similarity_score' => $similarityScore,
                            'similarity_factors' => $this->getSimilarityFactors($property, $candidate),
                            'calculated_at' => now(),
                        ]
                    );
                }
            }

            Log::info("Calculated similar properties for property {$this->propertyId}");
        } catch (\Exception $e) {
            Log::error("Failed to calculate similar properties for property {$this->propertyId}: ".$e->getMessage());
            throw $e;
        }
    }

    private function calculateSimilarity(Property $property1, Property $property2): float
    {
        $score = 0;
        $maxScore = 5;

        // Type similarity (20%)
        if ($property1->type === $property2->type) {
            $score += 1;
        }

        // Price similarity (20%)
        $priceDiff = abs($property1->price_per_night - $property2->price_per_night) / $property1->price_per_night;
        if ($priceDiff < 0.2) {
            $score += 1;
        } elseif ($priceDiff < 0.4) {
            $score += 0.5;
        }

        // Capacity similarity (20%)
        $capacityDiff = abs($property1->guests - $property2->guests);
        if ($capacityDiff === 0) {
            $score += 1;
        } elseif ($capacityDiff <= 2) {
            $score += 0.5;
        }

        // Bedrooms similarity (20%)
        $bedroomsDiff = abs($property1->bedrooms - $property2->bedrooms);
        if ($bedroomsDiff === 0) {
            $score += 1;
        } elseif ($bedroomsDiff <= 1) {
            $score += 0.5;
        }

        // Amenities similarity (20%)
        $amenities1 = $property1->amenities->pluck('id')->toArray();
        $amenities2 = $property2->amenities->pluck('id')->toArray();
        $overlap = count(array_intersect($amenities1, $amenities2));
        $total = count(array_unique(array_merge($amenities1, $amenities2)));
        if ($total > 0) {
            $score += ($overlap / $total);
        }

        return round(($score / $maxScore) * 100, 2);
    }

    private function getSimilarityFactors(Property $property1, Property $property2): array
    {
        return [
            'same_type' => $property1->type === $property2->type,
            'price_difference' => abs($property1->price_per_night - $property2->price_per_night),
            'capacity_difference' => abs($property1->guests - $property2->guests),
            'bedrooms_difference' => abs($property1->bedrooms - $property2->bedrooms),
            'same_city' => $property1->city === $property2->city,
        ];
    }
}
