<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SEOController extends Controller
{
    /**
     * Generate sitemap.xml
     */
    public function sitemap()
    {
        $sitemap = Cache::remember('sitemap', 3600, function () {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>';
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
            
            // Homepage
            $xml .= $this->addUrl(url('/'), now(), 'daily', '1.0');
            
            // Static pages
            $staticPages = [
                '/about',
                '/contact',
                '/faq',
                '/terms',
                '/privacy',
                '/properties',
            ];
            
            foreach ($staticPages as $page) {
                $xml .= $this->addUrl(url($page), now(), 'weekly', '0.8');
            }
            
            // Properties
            $properties = Property::where('status', 'active')->get();
            foreach ($properties as $property) {
                $xml .= $this->addUrl(
                    url('/properties/' . $property->slug),
                    $property->updated_at,
                    'weekly',
                    '0.9'
                );
            }
            
            // Locations
            $locations = Property::select('city', 'country')
                ->distinct()
                ->get();
            
            foreach ($locations as $location) {
                $slug = Str::slug($location->city . '-' . $location->country);
                $xml .= $this->addUrl(
                    url('/locations/' . $slug),
                    now(),
                    'weekly',
                    '0.7'
                );
            }
            
            // Blog posts
            if (class_exists(BlogPost::class)) {
                $posts = BlogPost::where('published', true)->get();
                foreach ($posts as $post) {
                    $xml .= $this->addUrl(
                        url('/blog/' . $post->slug),
                        $post->updated_at,
                        'monthly',
                        '0.6'
                    );
                }
            }
            
            $xml .= '</urlset>';
            
            return $xml;
        });
        
        return response($sitemap, 200)
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Generate robots.txt
     */
    public function robots()
    {
        $content = "User-agent: *\n";
        $content .= "Allow: /\n";
        $content .= "Disallow: /admin/\n";
        $content .= "Disallow: /api/\n";
        $content .= "Disallow: /dashboard/\n";
        $content .= "\n";
        $content .= "Sitemap: " . url('/sitemap.xml') . "\n";
        
        return response($content, 200)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Get meta tags for a property
     */
    public function propertyMeta(Property $property)
    {
        $meta = [
            'title' => $property->title . ' - RentHub',
            'description' => Str::limit($property->description, 160),
            'keywords' => implode(', ', [
                $property->property_type,
                $property->city,
                $property->country,
                'rental',
                'vacation rental',
                'property rental',
            ]),
            'og:title' => $property->title,
            'og:description' => Str::limit($property->description, 200),
            'og:image' => $property->images->first()?->url ?? url('/default-property.jpg'),
            'og:url' => url('/properties/' . $property->slug),
            'og:type' => 'website',
            'twitter:card' => 'summary_large_image',
            'twitter:title' => $property->title,
            'twitter:description' => Str::limit($property->description, 200),
            'twitter:image' => $property->images->first()?->url ?? url('/default-property.jpg'),
            'canonical' => url('/properties/' . $property->slug),
        ];
        
        return response()->json($meta);
    }

    /**
     * Get structured data (Schema.org) for a property
     */
    public function propertyStructuredData(Property $property)
    {
        $structuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $property->title,
            'description' => $property->description,
            'image' => $property->images->pluck('url')->toArray(),
            'brand' => [
                '@type' => 'Brand',
                'name' => 'RentHub'
            ],
            'offers' => [
                '@type' => 'Offer',
                'price' => $property->price,
                'priceCurrency' => 'USD',
                'availability' => 'https://schema.org/InStock',
                'url' => url('/properties/' . $property->slug),
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => $property->average_rating ?? 0,
                'reviewCount' => $property->reviews_count ?? 0,
            ],
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => $property->city,
                'addressCountry' => $property->country,
            ],
        ];
        
        return response()->json($structuredData);
    }

    /**
     * Add URL to sitemap
     */
    private function addUrl(string $loc, $lastmod, string $changefreq, string $priority): string
    {
        $xml = '<url>';
        $xml .= '<loc>' . htmlspecialchars($loc) . '</loc>';
        $xml .= '<lastmod>' . $lastmod->format('Y-m-d') . '</lastmod>';
        $xml .= '<changefreq>' . $changefreq . '</changefreq>';
        $xml .= '<priority>' . $priority . '</priority>';
        $xml .= '</url>';
        
        return $xml;
    }

    /**
     * Generate location landing pages
     */
    public function locationPage(string $slug)
    {
        [$city, $country] = explode('-', $slug, 2);
        $city = str_replace('-', ' ', $city);
        $country = str_replace('-', ' ', $country);
        
        $properties = Property::where('city', 'LIKE', "%{$city}%")
            ->where('country', 'LIKE', "%{$country}%")
            ->where('status', 'active')
            ->paginate(12);
        
        $meta = [
            'title' => "Vacation Rentals in {$city}, {$country} - RentHub",
            'description' => "Find the perfect vacation rental in {$city}, {$country}. Browse {$properties->total()} properties with great reviews and instant booking.",
            'canonical' => url('/locations/' . $slug),
        ];
        
        return response()->json([
            'properties' => $properties,
            'location' => ['city' => $city, 'country' => $country],
            'meta' => $meta,
        ]);
    }

    /**
     * Generate property type landing pages
     */
    public function propertyTypePage(string $type)
    {
        $typeName = str_replace('-', ' ', $type);
        
        $properties = Property::where('property_type', 'LIKE', "%{$typeName}%")
            ->where('status', 'active')
            ->paginate(12);
        
        $meta = [
            'title' => ucfirst($typeName) . " Rentals - RentHub",
            'description' => "Book your perfect {$typeName} rental. Browse {$properties->total()} verified properties with instant booking and great prices.",
            'canonical' => url('/property-types/' . $type),
        ];
        
        return response()->json([
            'properties' => $properties,
            'type' => $typeName,
            'meta' => $meta,
        ]);
    }
}
