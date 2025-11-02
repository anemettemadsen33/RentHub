import { useState } from 'react'
import { SearchForm, SearchParams } from '@/components/SearchForm'
import { PropertyCard } from '@/components/PropertyCard'
import { Button } from '@/components/ui/Button'
import { Layout } from '@/components/Layout'
import { useProperties } from '@/hooks/useProperties'
import { Property } from '@/types'
import { ArrowRight, Star, MapPin, Users } from 'lucide-react'
import Link from 'next/link'
import Head from 'next/head'

export default function HomePage() {
  const [searchParams, setSearchParams] = useState<SearchParams>({})
  const { 
    data: propertiesData, 
    isLoading, 
    error 
  } = useProperties(searchParams)

  const handleSearch = (params: SearchParams) => {
    setSearchParams(params)
  }

  const featuredProperties = propertiesData?.data?.filter((property: Property) => 
    property.is_featured
  )?.slice(0, 6) || []

  const recentProperties = propertiesData?.data?.slice(0, 8) || []

  return (
    <Layout>
      <Head>
        <title>RentHub - Găsește proprietatea perfectă pentru tine</title>
        <meta 
          name="description" 
          content="Descoperă cele mai bune proprietăți de închiriat în România. Apartamente, case, vile și multe altele te așteaptă pe RentHub." 
        />
      </Head>

      <div className="min-h-screen bg-gray-50">
        {/* Hero Section */}
        <section className="bg-gradient-to-br from-blue-600 to-blue-800 text-white">
          <div className="container mx-auto px-4 py-20">
            <div className="text-center mb-12">
              <h1 className="text-4xl md:text-6xl font-bold mb-6">
                Găsește casa ta de vacanță
              </h1>
              <p className="text-xl md:text-2xl mb-8 text-blue-100">
                Descoperă proprietăți unice și creează-ți amintiri de neuitat
              </p>
            </div>

            {/* Search Form */}
            <div className="max-w-4xl mx-auto">
              <SearchForm onSearch={handleSearch} />
            </div>
          </div>
        </section>

        {/* Stats Section */}
        <section className="py-16 bg-white">
          <div className="container mx-auto px-4">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
              <div>
                <div className="text-3xl font-bold text-blue-600 mb-2">10,000+</div>
                <div className="text-gray-600">Proprietăți disponibile</div>
              </div>
              <div>
                <div className="text-3xl font-bold text-blue-600 mb-2">50,000+</div>
                <div className="text-gray-600">Oaspeți mulțumiți</div>
              </div>
              <div>
                <div className="text-3xl font-bold text-blue-600 mb-2">100+</div>
                <div className="text-gray-600">Orașe acoperite</div>
              </div>
            </div>
          </div>
        </section>

        {/* Featured Properties */}
        {featuredProperties.length > 0 && (
          <section className="py-16">
            <div className="container mx-auto px-4">
              <div className="flex justify-between items-center mb-12">
                <div>
                  <h2 className="text-3xl font-bold text-gray-900 mb-2">
                    Proprietăți în evidență
                  </h2>
                  <p className="text-gray-600">
                    Descoperă cele mai populare proprietăți selectate de echipa noastră
                  </p>
                </div>
                <Link href="/properties">
                  <Button variant="outline">
                    Vezi toate
                    <ArrowRight className="ml-2 h-4 w-4" />
                  </Button>
                </Link>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                {featuredProperties.map((property: Property) => (
                  <PropertyCard key={property.id} property={property} />
                ))}
              </div>
            </div>
          </section>
        )}

        {/* Recent Properties */}
        <section className="py-16 bg-gray-50">
          <div className="container mx-auto px-4">
            <div className="flex justify-between items-center mb-12">
              <div>
                <h2 className="text-3xl font-bold text-gray-900 mb-2">
                  Adăugate recent
                </h2>
                <p className="text-gray-600">
                  Cele mai noi proprietăți adăugate pe platforma noastră
                </p>
              </div>
              <Link href="/properties">
                <Button variant="outline">
                  Vezi toate
                  <ArrowRight className="ml-2 h-4 w-4" />
                </Button>
              </Link>
            </div>

            {isLoading ? (
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                {[...Array(8)].map((_, i) => (
                  <div key={i} className="animate-pulse">
                    <div className="bg-gray-300 h-48 rounded-lg mb-4"></div>
                    <div className="bg-gray-300 h-4 rounded mb-2"></div>
                    <div className="bg-gray-300 h-4 rounded w-2/3"></div>
                  </div>
                ))}
              </div>
            ) : error ? (
              <div className="text-center py-8">
                <p className="text-gray-500">Nu am putut încărca proprietățile. Te rugăm să încerci din nou.</p>
              </div>
            ) : (
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                {recentProperties.map((property: Property) => (
                  <PropertyCard key={property.id} property={property} />
                ))}
              </div>
            )}
          </div>
        </section>

        {/* Features Section */}
        <section className="py-16 bg-white">
          <div className="container mx-auto px-4">
            <div className="text-center mb-12">
              <h2 className="text-3xl font-bold text-gray-900 mb-4">
                De ce să alegi RentHub?
              </h2>
              <p className="text-gray-600 max-w-2xl mx-auto">
                Oferim cea mai bună experiență pentru găsirea și închirierea proprietăților
              </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
              <div className="text-center">
                <div className="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                  <Star className="h-8 w-8 text-blue-600" />
                </div>
                <h3 className="text-xl font-semibold mb-2">Proprietăți verificate</h3>
                <p className="text-gray-600">
                  Toate proprietățile sunt verificate și validate de echipa noastră
                </p>
              </div>

              <div className="text-center">
                <div className="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                  <MapPin className="h-8 w-8 text-blue-600" />
                </div>
                <h3 className="text-xl font-semibold mb-2">Locații diverse</h3>
                <p className="text-gray-600">
                  Acoperim toate orașele importante din România cu proprietăți variate
                </p>
              </div>

              <div className="text-center">
                <div className="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                  <Users className="h-8 w-8 text-blue-600" />
                </div>
                <h3 className="text-xl font-semibold mb-2">Suport 24/7</h3>
                <p className="text-gray-600">
                  Echipa noastră este disponibilă oricând pentru a te ajuta
                </p>
              </div>
            </div>
          </div>
        </section>
      </div>
    </Layout>
  )
}