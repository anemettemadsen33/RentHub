<?php

namespace App\Services;

use App\Models\Property;
use Illuminate\Support\Str;

class SEOService
{
    public function generatePropertyMeta(Property $property): array
    {
        return [
            'title' => $this->generateTitle($property),
            'description' => $this->generateDescription($property),
            'keywords' => $this->generateKeywords($property),
            'og_title' => $this->generateOgTitle($property),
            'og_description' => $this->generateOgDescription($property),
            'og_image' => $this->getPropertyImage($property),
            'og_type' => 'product',
            'og_url' => route('properties.show', $property->slug),
            'twitter_card' => 'summary_large_image',
            'twitter_title' => $this->generateTitle($property),
            'twitter_description' => $this->generateDescription($property),
            'twitter_image' => $this->getPropertyImage($property),
            'canonical' => route('properties.show', $property->slug),
            'schema' => $this->generatePropertySchema($property),
        ];
    }

    public function generatePropertySchema(Property $property): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $property->title,
            'description' => $property->description,
            'image' => $this->getPropertyImage($property),
            'brand' => [
                '@type' => 'Brand',
                'name' => config('app.name'),
            ],
            'offers' => [
                '@type' => 'Offer',
                'price' => $property->price_per_night,
                'priceCurrency' => $property->currency ?? 'USD',
                'availability' => 'https://schema.org/InStock',
                'url' => route('properties.show', $property->slug),
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => $property->average_rating ?? 0,
                'reviewCount' => $property->reviews_count ?? 0,
            ],
        ];
    }

    public function generateBreadcrumbs(array $items): array
    {
        $breadcrumbs = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [],
        ];

        foreach ($items as $index => $item) {
            $breadcrumbs['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
                'item' => $item['url'],
            ];
        }

        return $breadcrumbs;
    }

    public function generateSitemap(): string
    {
        $properties = Property::where('status', 'active')->get();
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
        // Homepage
        $xml .= '<url>';
        $xml .= '<loc>' . url('/') . '</loc>';
        $xml .= '<changefreq>daily</changefreq>';
        $xml .= '<priority>1.0</priority>';
        $xml .= '</url>';
        
        // Properties
        foreach ($properties as $property) {
            $xml .= '<url>';
            $xml .= '<loc>' . route('properties.show', $property->slug) . '</loc>';
            $xml .= '<lastmod>' . $property->updated_at->toAtomString() . '</lastmod>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.8</priority>';
            $xml .= '</url>';
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }

    public function generateRobotsTxt(): string
    {
        $robots = "User-agent: *\n";
        $robots .= "Disallow: /admin/\n";
        $robots .= "Disallow: /api/\n";
        $robots .= "Disallow: /dashboard/\n";
        $robots .= "Allow: /\n";
        $robots .= "\n";
        $robots .= "Sitemap: " . url('/sitemap.xml') . "\n";
        
        return $robots;
    }

    protected function generateTitle(Property $property): string
    {
        return Str::limit($property->title . ' | ' . config('app.name'), 60);
    }

    protected function generateDescription(Property $property): string
    {
        return Str::limit($property->description, 160);
    }

    protected function generateKeywords(Property $property): string
    {
        $keywords = [
            $property->property_type,
            $property->city,
            $property->country,
            'vacation rental',
            'property rental',
        ];

        if ($property->amenities) {
            $keywords = array_merge($keywords, array_slice($property->amenities, 0, 5));
        }

        return implode(', ', array_filter($keywords));
    }

    protected function generateOgTitle(Property $property): string
    {
        return $property->title;
    }

    protected function generateOgDescription(Property $property): string
    {
        return Str::limit($property->description, 200);
    }

    protected function getPropertyImage(Property $property): string
    {
        if ($property->images && count($property->images) > 0) {
            return $property->images[0]['url'] ?? url('/images/default-property.jpg');
        }

        return url('/images/default-property.jpg');
    }
}
