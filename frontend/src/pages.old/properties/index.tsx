import { useState, useEffect } from 'react'
import { SearchForm, SearchParams } from '@/components/SearchForm'
import { PropertyCard } from '@/components/PropertyCard'
import { Button } from '@/components/ui/Button'
import { useProperties } from '@/hooks/useProperties'
import { Property } from '@/types'
import { Filter, Grid, List, ChevronLeft, ChevronRight } from 'lucide-react'
import Head from 'next/head'
import { Layout } from '@/components/Layout'

export default function PropertiesPage() {
  const [searchParams, setSearchParams] = useState<SearchParams>({})
  const [currentPage, setCurrentPage] = useState(1)
  const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid')
  const [showFilters, setShowFilters] = useState(false)

  const { 
    data: propertiesData, 
    isLoading, 
    error 
  } = useProperties({ ...searchParams, page: currentPage })

  const handleSearch = (params: SearchParams) => {
    setSearchParams(params)
    setCurrentPage(1)
  }

  const properties = propertiesData?.data || []
  const totalPages = propertiesData?.last_page || 1
  const totalProperties = propertiesData?.total || 0

  const handlePreviousPage = () => {
    if (currentPage > 1) {
      setCurrentPage(currentPage - 1)
    }
  }

  const handleNextPage = () => {
    if (currentPage < totalPages) {
      setCurrentPage(currentPage + 1)
    }
  }

  useEffect(() => {
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }, [currentPage])

  return (
    <Layout>
      <Head>
        <title>Proprietăți de închiriat - RentHub</title>
        <meta 
          name="description" 
          content="Explorează o gamă largă de proprietăți de închiriat în România. Găsește apartamentul, casa sau vila perfectă pentru tine." 
        />
      </Head>

      <div className="min-h-screen bg-gray-50">
        {/* Page Header */}
        <section className="bg-white shadow-sm">
          <div className="container mx-auto px-4 py-8">
            <h1 className="text-3xl font-bold text-gray-900 mb-4">
              Proprietăți de închiriat
            </h1>
            <p className="text-gray-600">
              Descoperă {totalProperties.toLocaleString('ro-RO')} proprietăți disponibile în România
            </p>
          </div>
        </section>

        {/* Search and Filters */}
        <section className="bg-white border-b">
          <div className="container mx-auto px-4 py-6">
            <SearchForm onSearch={handleSearch} />
            
            {/* View Controls */}
            <div className="flex justify-between items-center mt-6">
              <div className="flex items-center gap-4">
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => setShowFilters(!showFilters)}
                >
                  <Filter className="h-4 w-4 mr-2" />
                  Filtrează
                </Button>
                
                <span className="text-sm text-gray-500">
                  {totalProperties.toLocaleString('ro-RO')} proprietăți găsite
                </span>
              </div>
              
              <div className="flex items-center gap-2">
                <Button
                  variant={viewMode === 'grid' ? 'default' : 'outline'}
                  size="sm"
                  onClick={() => setViewMode('grid')}
                >
                  <Grid className="h-4 w-4" />
                </Button>
                <Button
                  variant={viewMode === 'list' ? 'default' : 'outline'}
                  size="sm"
                  onClick={() => setViewMode('list')}
                >
                  <List className="h-4 w-4" />
                </Button>
              </div>
            </div>
          </div>
        </section>

        {/* Properties Grid/List */}
        <section className="py-8">
          <div className="container mx-auto px-4">
            {isLoading ? (
              <div className={`grid gap-6 ${viewMode === 'grid' ? 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3' : 'grid-cols-1'}`}>
                {[...Array(9)].map((_, i) => (
                  <div key={i} className="animate-pulse">
                    <div className="bg-gray-300 h-64 rounded-lg mb-4"></div>
                    <div className="space-y-2">
                      <div className="bg-gray-300 h-4 rounded"></div>
                      <div className="bg-gray-300 h-4 rounded w-2/3"></div>
                      <div className="bg-gray-300 h-4 rounded w-1/2"></div>
                    </div>
                  </div>
                ))}
              </div>
            ) : error ? (
              <div className="text-center py-12">
                <p className="text-gray-500 mb-4">
                  Nu am putut încărca proprietățile. Te rugăm să încerci din nou.
                </p>
                <Button onClick={() => window.location.reload()}>
                  Încearcă din nou
                </Button>
              </div>
            ) : properties.length === 0 ? (
              <div className="text-center py-12">
                <p className="text-gray-500 mb-4">
                  Nu am găsit proprietăți care să corespundă criteriilor tale de căutare.
                </p>
                <Button onClick={() => setSearchParams({})}>
                  Șterge filtrele
                </Button>
              </div>
            ) : (
              <>
                <div className={`grid gap-6 ${
                  viewMode === 'grid' 
                    ? 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3' 
                    : 'grid-cols-1'
                }`}>
                  {properties.map((property: Property) => (
                    <PropertyCard key={property.id} property={property} />
                  ))}
                </div>

                {/* Pagination */}
                {totalPages > 1 && (
                  <div className="flex justify-center items-center mt-12 space-x-4">
                    <Button
                      variant="outline"
                      onClick={handlePreviousPage}
                      disabled={currentPage === 1}
                    >
                      <ChevronLeft className="h-4 w-4 mr-2" />
                      Anterior
                    </Button>

                    <div className="flex items-center space-x-2">
                      {[...Array(Math.min(5, totalPages))].map((_, i) => {
                        let pageNum
                        if (totalPages <= 5) {
                          pageNum = i + 1
                        } else if (currentPage <= 3) {
                          pageNum = i + 1
                        } else if (currentPage >= totalPages - 2) {
                          pageNum = totalPages - 4 + i
                        } else {
                          pageNum = currentPage - 2 + i
                        }

                        return (
                          <Button
                            key={pageNum}
                            variant={currentPage === pageNum ? 'default' : 'outline'}
                            size="sm"
                            onClick={() => setCurrentPage(pageNum)}
                          >
                            {pageNum}
                          </Button>
                        )
                      })}
                    </div>

                    <Button
                      variant="outline"
                      onClick={handleNextPage}
                      disabled={currentPage === totalPages}
                    >
                      Următor
                      <ChevronRight className="h-4 w-4 ml-2" />
                    </Button>
                  </div>
                )}

                {/* Results Summary */}
                <div className="text-center mt-8 text-sm text-gray-500">
                  Pagina {currentPage} din {totalPages} ({totalProperties.toLocaleString('ro-RO')} proprietăți)
                </div>
              </>
            )}
          </div>
        </section>
      </div>
    </Layout>
  )
}