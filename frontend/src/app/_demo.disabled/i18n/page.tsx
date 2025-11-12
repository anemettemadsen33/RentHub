'use client';

import { useTranslations, useLocale } from 'next-intl';
import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { LanguageSwitcher } from '@/components/language-switcher';
import { 
  Globe, 
  Languages, 
  CheckCircle2,
  Code2,
  Zap,
  BookOpen,
} from 'lucide-react';

export default function I18nDemoPage() {
  const t = useTranslations();
  const locale = useLocale();

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-12 max-w-6xl">
        {/* Header */}
        <div className="text-center mb-12">
          <Badge className="mb-4">Internationalization (i18n)</Badge>
          <h1 className="text-4xl font-bold mb-4">
            {t('common.loading')} - Multi-Language Support
          </h1>
          <p className="text-xl text-muted-foreground max-w-2xl mx-auto">
            Current locale: <strong>{locale === 'ro' ? '🇷🇴 Română' : '🇬🇧 English'}</strong>
          </p>
          <div className="mt-4">
            <LanguageSwitcher />
          </div>
        </div>

        {/* Features Overview */}
        <Card className="mb-8">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <CheckCircle2 className="h-5 w-5 text-green-500" />
              Key Features
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="flex items-start gap-3">
                <Globe className="h-5 w-5 text-blue-500 mt-0.5 flex-shrink-0" />
                <div>
                  <p className="font-semibold">Multiple Languages</p>
                  <p className="text-sm text-muted-foreground">English & Romanian support with easy extension</p>
                </div>
              </div>
              <div className="flex items-start gap-3">
                <Zap className="h-5 w-5 text-yellow-500 mt-0.5 flex-shrink-0" />
                <div>
                  <p className="font-semibold">Type-Safe Translations</p>
                  <p className="text-sm text-muted-foreground">TypeScript autocomplete for all translation keys</p>
                </div>
              </div>
              <div className="flex items-start gap-3">
                <Languages className="h-5 w-5 text-purple-500 mt-0.5 flex-shrink-0" />
                <div>
                  <p className="font-semibold">Dynamic Language Switching</p>
                  <p className="text-sm text-muted-foreground">Switch languages without page reload</p>
                </div>
              </div>
              <div className="flex items-start gap-3">
                <BookOpen className="h-5 w-5 text-green-500 mt-0.5 flex-shrink-0" />
                <div>
                  <p className="font-semibold">Organized Translation Files</p>
                  <p className="text-sm text-muted-foreground">JSON-based translation management</p>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Translation Examples */}
        <Card className="mb-8">
          <CardHeader>
            <CardTitle>1. Common Translations</CardTitle>
            <CardDescription>
              Basic UI elements translated dynamically
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
              <Button variant="outline">{t('common.save')}</Button>
              <Button variant="outline">{t('common.cancel')}</Button>
              <Button variant="outline">{t('common.delete')}</Button>
              <Button variant="outline">{t('common.edit')}</Button>
              <Button variant="outline">{t('common.search')}</Button>
              <Button variant="outline">{t('common.filter')}</Button>
              <Button variant="outline">{t('common.submit')}</Button>
              <Button variant="outline">{t('common.confirm')}</Button>
            </div>
          </CardContent>
        </Card>

        {/* Navigation Translations */}
        <Card className="mb-8">
          <CardHeader>
            <CardTitle>2. Navigation</CardTitle>
            <CardDescription>
              Menu items and navigation elements
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="flex flex-wrap gap-2">
              {['home', 'properties', 'bookings', 'messages', 'notifications', 'favorites', 'profile', 'settings'].map((key) => (
                <Badge key={key} variant="secondary">
                  {t(`navigation.${key}`)}
                </Badge>
              ))}
            </div>
          </CardContent>
        </Card>

        {/* Auth Translations */}
        <Card className="mb-8">
          <CardHeader>
            <CardTitle>3. Authentication Forms</CardTitle>
            <CardDescription>
              Login and registration text
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div>
              <h4 className="font-semibold mb-2">{t('auth.login.title')}</h4>
              <p className="text-sm text-muted-foreground">{t('auth.login.subtitle')}</p>
              <div className="mt-3 space-y-2">
                <div className="flex gap-2">
                  <Badge variant="outline">{t('auth.login.email')}</Badge>
                  <Badge variant="outline">{t('auth.login.password')}</Badge>
                  <Badge variant="outline">{t('auth.login.rememberMe')}</Badge>
                </div>
                <Button className="mt-2">{t('auth.login.submit')}</Button>
              </div>
            </div>
            <div className="border-t pt-4">
              <h4 className="font-semibold mb-2">{t('auth.register.title')}</h4>
              <p className="text-sm text-muted-foreground">{t('auth.register.subtitle')}</p>
              <div className="mt-3 space-y-2">
                <div className="flex gap-2 flex-wrap">
                  <Badge variant="outline">{t('auth.register.name')}</Badge>
                  <Badge variant="outline">{t('auth.register.email')}</Badge>
                  <Badge variant="outline">{t('auth.register.password')}</Badge>
                  <Badge variant="outline">{t('auth.register.confirmPassword')}</Badge>
                </div>
                <Button className="mt-2">{t('auth.register.submit')}</Button>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Properties Translations */}
        <Card className="mb-8">
          <CardHeader>
            <CardTitle>4. Properties Section</CardTitle>
            <CardDescription>
              Property listing and search translations
            </CardDescription>
          </CardHeader>
          <CardContent>
            <h3 className="text-lg font-semibold mb-3">{t('properties.title')}</h3>
            <div className="space-y-3">
              <div className="flex gap-2">
                <Badge>{t('properties.sort.newest')}</Badge>
                <Badge>{t('properties.sort.priceAsc')}</Badge>
                <Badge>{t('properties.sort.priceDesc')}</Badge>
                <Badge>{t('properties.sort.rating')}</Badge>
              </div>
              <div className="flex gap-2">
                <Button variant="outline" size="sm">{t('properties.viewMode.grid')}</Button>
                <Button variant="outline" size="sm">{t('properties.viewMode.list')}</Button>
                <Button variant="outline" size="sm">{t('properties.viewMode.map')}</Button>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Parameterized Translations */}
        <Card className="mb-8">
          <CardHeader>
            <CardTitle>5. Parameterized Translations</CardTitle>
            <CardDescription>
              Dynamic values in translations
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-3">
            <div>
              <p className="text-sm font-semibold mb-1">Examples:</p>
              <div className="space-y-1 text-sm">
                <p>• {t('properties.guests', { count: 4 })}</p>
                <p>• {t('properties.bedrooms', { count: 2 })}</p>
                <p>• {t('properties.bathrooms', { count: 3 })}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Code Examples */}
        <Card className="mb-8">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Code2 className="h-5 w-5" />
              Usage Examples
            </CardTitle>
            <CardDescription>How to use translations in your code</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div>
              <h4 className="font-semibold mb-2">1. Basic Translation:</h4>
              <pre className="bg-muted p-4 rounded-lg overflow-x-auto text-sm">
{`import { useTranslations } from 'next-intl';

export function MyComponent() {
  const t = useTranslations();
  
  return (
    <Button>{t('common.save')}</Button>
  );
}`}
              </pre>
            </div>

            <div>
              <h4 className="font-semibold mb-2">2. Namespaced Translations:</h4>
              <pre className="bg-muted p-4 rounded-lg overflow-x-auto text-sm">
{`import { useTranslations } from 'next-intl';

export function LoginForm() {
  const t = useTranslations('auth.login');
  
  return (
    <>
      <h1>{t('title')}</h1>
      <p>{t('subtitle')}</p>
      <Button>{t('submit')}</Button>
    </>
  );
}`}
              </pre>
            </div>

            <div>
              <h4 className="font-semibold mb-2">3. Parameterized Translations:</h4>
              <pre className="bg-muted p-4 rounded-lg overflow-x-auto text-sm">
{`import { useTranslations } from 'next-intl';

export function PropertyCard({ property }) {
  const t = useTranslations('properties');
  
  return (
    <div>
      <p>{t('guests', { count: property.maxGuests })}</p>
      <p>{t('bedrooms', { count: property.bedrooms })}</p>
    </div>
  );
}`}
              </pre>
            </div>

            <div>
              <h4 className="font-semibold mb-2">4. Get Current Locale:</h4>
              <pre className="bg-muted p-4 rounded-lg overflow-x-auto text-sm">
{`import { useLocale } from 'next-intl';

export function MyComponent() {
  const locale = useLocale(); // 'en' or 'ro'
  
  return <div>Current language: {locale}</div>;
}`}
              </pre>
            </div>
          </CardContent>
        </Card>

        {/* Benefits */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Zap className="h-5 w-5 text-yellow-500" />
              Benefits
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="bg-green-50 dark:bg-green-950 p-4 rounded-lg">
                <p className="font-semibold text-green-700 dark:text-green-300 mb-2">✅ Type Safety</p>
                <p className="text-sm text-muted-foreground">
                  Full TypeScript support with autocomplete for translation keys
                </p>
              </div>
              <div className="bg-blue-50 dark:bg-blue-950 p-4 rounded-lg">
                <p className="font-semibold text-blue-700 dark:text-blue-300 mb-2">🚀 Performance</p>
                <p className="text-sm text-muted-foreground">
                  Only loads translations for active locale
                </p>
              </div>
              <div className="bg-purple-50 dark:bg-purple-950 p-4 rounded-lg">
                <p className="font-semibold text-purple-700 dark:text-purple-300 mb-2">🔄 Dynamic</p>
                <p className="text-sm text-muted-foreground">
                  Switch languages without page reload
                </p>
              </div>
              <div className="bg-orange-50 dark:bg-orange-950 p-4 rounded-lg">
                <p className="font-semibold text-orange-700 dark:text-orange-300 mb-2">📦 Scalable</p>
                <p className="text-sm text-muted-foreground">
                  Easy to add new languages - just add JSON file
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </MainLayout>
  );
}
