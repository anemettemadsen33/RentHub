'use client';

import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import Image from 'next/image';
import { ImageWithFallback } from '@/components/ui/image-with-fallback';
import { CheckCircle, Zap, ImageIcon, Gauge } from 'lucide-react';

export default function ImageOptimizationDemo() {
  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-12 max-w-6xl">
        {/* Header */}
        <div className="text-center mb-12">
          <Badge className="mb-4">Next.js Image Optimization</Badge>
          <h1 className="text-4xl font-bold mb-4">Image Optimization Demo</h1>
          <p className="text-xl text-muted-foreground max-w-2xl mx-auto">
            Live examples of Next.js Image component with automatic WebP/AVIF conversion, lazy loading, and responsive sizing
          </p>
        </div>

        {/* Benefits */}
        <Card className="mb-8">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Zap className="h-5 w-5 text-yellow-500" />
              Optimization Benefits
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <div className="flex items-start gap-3">
                <CheckCircle className="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" />
                <div>
                  <p className="font-semibold">Automatic Format Conversion</p>
                  <p className="text-sm text-muted-foreground">WebP/AVIF for 30-50% smaller files</p>
                </div>
              </div>
              <div className="flex items-start gap-3">
                <CheckCircle className="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" />
                <div>
                  <p className="font-semibold">Lazy Loading</p>
                  <p className="text-sm text-muted-foreground">Only load images when visible</p>
                </div>
              </div>
              <div className="flex items-start gap-3">
                <CheckCircle className="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" />
                <div>
                  <p className="font-semibold">Responsive Sizing</p>
                  <p className="text-sm text-muted-foreground">Serve optimal size per device</p>
                </div>
              </div>
              <div className="flex items-start gap-3">
                <CheckCircle className="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" />
                <div>
                  <p className="font-semibold">Priority Loading</p>
                  <p className="text-sm text-muted-foreground">Faster LCP for hero images</p>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Demo 1: Basic Image */}
        <Card className="mb-8">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <ImageIcon className="h-5 w-5" />
              1. Basic Next.js Image
            </CardTitle>
            <CardDescription>
              Standard image with automatic optimization, lazy loading, and responsive sizing
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
              <div>
                <h4 className="font-semibold mb-2">Old Method (&lt;img&gt;):</h4>
                <pre className="bg-muted p-4 rounded-lg text-sm overflow-x-auto">
{`<img
  src="https://images.unsplash.com/..."
  alt="Property"
  className="w-full h-64 object-cover"
/>`}
                </pre>
                <p className="text-sm text-muted-foreground mt-2">
                  ❌ No optimization, no lazy loading, no modern formats
                </p>
              </div>
              <div>
                <h4 className="font-semibold mb-2">New Method (Next.js Image):</h4>
                <pre className="bg-muted p-4 rounded-lg text-sm overflow-x-auto">
{`<Image
  src="https://images.unsplash.com/..."
  alt="Property"
  fill
  className="object-cover"
  sizes="(max-width: 768px) 100vw, 50vw"
  loading="lazy"
/>`}
                </pre>
                <p className="text-sm text-green-600 mt-2">
                  ✅ Auto WebP/AVIF, lazy loading, responsive sizing
                </p>
              </div>
            </div>

            <div className="mt-6">
              <h4 className="font-semibold mb-3">Live Example:</h4>
              <div className="relative h-64 rounded-lg overflow-hidden bg-gray-100">
                <Image
                  src="https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=800"
                  alt="Modern apartment building"
                  fill
                  className="object-cover"
                  sizes="(max-width: 768px) 100vw, 800px"
                  loading="lazy"
                />
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Demo 2: Priority Loading */}
        <Card className="mb-8">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Gauge className="h-5 w-5 text-blue-500" />
              2. Priority Loading for Hero Images
            </CardTitle>
            <CardDescription>
              Faster LCP (Largest Contentful Paint) for above-the-fold images
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              <div className="bg-muted p-4 rounded-lg">
                <pre className="text-sm overflow-x-auto">
{`<Image
  src={heroImage}
  alt="Hero"
  fill
  priority  // 👈 Loads immediately, no lazy loading
  className="object-cover"
  sizes="100vw"
/>`}
                </pre>
              </div>
              <div className="relative h-96 rounded-lg overflow-hidden bg-gray-100">
                <Image
                  src="https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=1200"
                  alt="Luxury property hero image"
                  fill
                  priority
                  className="object-cover"
                  sizes="100vw"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex items-end">
                  <div className="p-8 text-white">
                    <Badge className="mb-2 bg-blue-500">Priority Loading</Badge>
                    <h3 className="text-3xl font-bold mb-2">Luxury Beachfront Villa</h3>
                    <p className="text-white/90">Loaded with priority for fastest LCP score</p>
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Demo 3: Image with Fallback */}
        <Card className="mb-8">
          <CardHeader>
            <CardTitle>3. Image with Fallback</CardTitle>
            <CardDescription>
              Graceful error handling with automatic placeholder on load failure
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <h4 className="font-semibold mb-3">Working Image:</h4>
                <div className="relative h-48 rounded-lg overflow-hidden bg-gray-100">
                  <ImageWithFallback
                    src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=600"
                    alt="Cozy living room"
                    fill
                    className="object-cover"
                    sizes="300px"
                  />
                </div>
                <p className="text-sm text-green-600 mt-2">✅ Image loaded successfully</p>
              </div>
              <div>
                <h4 className="font-semibold mb-3">Broken URL (with fallback):</h4>
                <div className="relative h-48 rounded-lg overflow-hidden bg-gray-100">
                  <ImageWithFallback
                    src="https://invalid-url.example.com/broken.jpg"
                    fallbackSrc="https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=600"
                    alt="Failed image with fallback"
                    fill
                    className="object-cover"
                    sizes="300px"
                  />
                </div>
                <p className="text-sm text-yellow-600 mt-2">⚠️ Fallback placeholder displayed</p>
              </div>
            </div>

            <div className="mt-6 bg-muted p-4 rounded-lg">
              <pre className="text-sm overflow-x-auto">
{`<ImageWithFallback
  src={userAvatar}
  fallbackSrc="/default-avatar.png"
  alt="User avatar"
  width={48}
  height={48}
  onError={() => console.log('Image failed to load')}
/>`}
              </pre>
            </div>
          </CardContent>
        </Card>

        {/* Demo 4: Responsive Images */}
        <Card className="mb-8">
          <CardHeader>
            <CardTitle>4. Responsive Image Gallery</CardTitle>
            <CardDescription>
              Different sizes served based on viewport width using the sizes attribute
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
              {[
                'photo-1600585154340-be6161a56a0c',
                'photo-1600566753190-17f0baa2a6c3',
                'photo-1600607687939-ce8a6c25118c',
              ].map((id, index) => (
                <div key={index} className="relative h-48 rounded-lg overflow-hidden bg-gray-100">
                  <Image
                    src={`https://images.unsplash.com/${id}?w=400`}
                    alt={`Property ${index + 1}`}
                    fill
                    className="object-cover hover:scale-110 transition-transform duration-300"
                    sizes="(max-width: 768px) 100vw, 33vw"
                    loading="lazy"
                  />
                </div>
              ))}
            </div>
            <div className="bg-muted p-4 rounded-lg">
              <p className="text-sm font-semibold mb-2">Sizes breakdown:</p>
              <ul className="text-sm space-y-1 text-muted-foreground">
                <li>📱 Mobile (≤768px): 100vw (full width)</li>
                <li>💻 Desktop (&gt;768px): 33vw (1/3 width)</li>
                <li>🎯 Next.js automatically serves optimal image size</li>
              </ul>
            </div>
          </CardContent>
        </Card>

        {/* Performance Impact */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Zap className="h-5 w-5 text-yellow-500" />
              Performance Impact
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div className="text-center p-6 bg-green-50 dark:bg-green-950 rounded-lg">
                <p className="text-4xl font-bold text-green-600 mb-2">30-50%</p>
                <p className="text-sm text-muted-foreground">Smaller file sizes with WebP/AVIF</p>
              </div>
              <div className="text-center p-6 bg-blue-50 dark:bg-blue-950 rounded-lg">
                <p className="text-4xl font-bold text-blue-600 mb-2">2-3x</p>
                <p className="text-sm text-muted-foreground">Faster initial page load</p>
              </div>
              <div className="text-center p-6 bg-purple-50 dark:bg-purple-950 rounded-lg">
                <p className="text-4xl font-bold text-purple-600 mb-2">90+</p>
                <p className="text-sm text-muted-foreground">Lighthouse performance score</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </MainLayout>
  );
}
