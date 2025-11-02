import { useEffect } from 'react'
import { useRouter } from 'next/router'
import Head from 'next/head'
import { Layout } from '@/components/Layout'
import { Button } from '@/components/ui/Button'
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/Card'
import { useAuth } from '@/hooks/useAuth'
import { User, Home, Calendar, Heart, Settings, Plus } from 'lucide-react'
import Link from 'next/link'

export default function DashboardPage() {
  const { user, isAuthenticated, logout } = useAuth()
  const router = useRouter()

  useEffect(() => {
    if (!isAuthenticated) {
      router.push('/auth/login')
    }
  }, [isAuthenticated, router])

  if (!user) {
    return (
      <Layout>
        <div className="min-h-screen bg-gray-50 flex items-center justify-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        </div>
      </Layout>
    )
  }

  const handleLogout = async () => {
    await logout()
    router.push('/')
  }

  return (
    <Layout>
      <Head>
        <title>Dashboard - RentHub</title>
        <meta name="description" content="Panoul tău de control RentHub" />
      </Head>

      <div className="min-h-screen bg-gray-50">
        {/* Header */}
        <div className="bg-white shadow-sm">
          <div className="container mx-auto px-4 py-8">
            <div className="flex items-center justify-between">
              <div>
                <h1 className="text-3xl font-bold text-gray-900">
                  Bună ziua, {user.name}!
                </h1>
                <p className="text-gray-600 mt-1">
                  Bun venit în dashboard-ul tău RentHub
                </p>
              </div>
              <div className="flex items-center gap-2">
                <span className={`px-3 py-1 rounded-full text-sm font-medium ${
                  user.role === 'owner' 
                    ? 'bg-blue-100 text-blue-800' 
                    : 'bg-green-100 text-green-800'
                }`}>
                  {user.role === 'owner' ? 'Proprietar' : 'Oaspete'}
                </span>
                <Button variant="outline" onClick={handleLogout}>
                  Deconectare
                </Button>
              </div>
            </div>
          </div>
        </div>

        {/* Main Content */}
        <div className="container mx-auto px-4 py-8">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            {/* Profile Card */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <User className="h-5 w-5" />
                  Profilul meu
                </CardTitle>
                <CardDescription>
                  Gestionează informațiile tale personale
                </CardDescription>
              </CardHeader>
              <CardContent>
                <div className="space-y-2 text-sm">
                  <div>
                    <span className="font-medium">Email:</span> {user.email}
                  </div>
                  <div>
                    <span className="font-medium">Status:</span>{' '}
                    <span className={user.is_verified ? 'text-green-600' : 'text-orange-600'}>
                      {user.is_verified ? 'Verificat' : 'Neverificat'}
                    </span>
                  </div>
                </div>
                <Link href="/profile" className="mt-4 block">
                  <Button variant="outline" size="sm" className="w-full">
                    Editează profilul
                  </Button>
                </Link>
              </CardContent>
            </Card>

            {/* Properties Card (for owners) */}
            {user.role === 'owner' && (
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <Home className="h-5 w-5" />
                    Proprietățile mele
                  </CardTitle>
                  <CardDescription>
                    Gestionează-ți proprietățile
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <p className="text-sm text-gray-600 mb-4">
                    Adaugă și gestionează proprietățile tale pentru închiriere.
                  </p>
                  <div className="space-y-2">
                    <Link href="/properties/add">
                      <Button size="sm" className="w-full">
                        <Plus className="h-4 w-4 mr-2" />
                        Adaugă proprietate
                      </Button>
                    </Link>
                    <Link href="/my-properties">
                      <Button variant="outline" size="sm" className="w-full">
                        Proprietățile mele
                      </Button>
                    </Link>
                  </div>
                </CardContent>
              </Card>
            )}

            {/* Bookings Card */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Calendar className="h-5 w-5" />
                  {user.role === 'owner' ? 'Rezervări primite' : 'Rezervările mele'}
                </CardTitle>
                <CardDescription>
                  {user.role === 'owner' 
                    ? 'Gestionează rezervările pentru proprietățile tale'
                    : 'Vezi și gestionează rezervările tale'
                  }
                </CardDescription>
              </CardHeader>
              <CardContent>
                <p className="text-sm text-gray-600 mb-4">
                  Nu ai rezervări active momentan.
                </p>
                <Link href="/bookings">
                  <Button variant="outline" size="sm" className="w-full">
                    Vezi rezervările
                  </Button>
                </Link>
              </CardContent>
            </Card>

            {/* Favorites Card (for guests) */}
            {user.role === 'guest' && (
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <Heart className="h-5 w-5" />
                    Favorite
                  </CardTitle>
                  <CardDescription>
                    Proprietățile tale salvate
                  </CardDescription>
                </CardHeader>
                <CardContent>
                  <p className="text-sm text-gray-600 mb-4">
                    Nu ai proprietăți favorite momentan.
                  </p>
                  <Link href="/favorites">
                    <Button variant="outline" size="sm" className="w-full">
                      Vezi favoritele
                    </Button>
                  </Link>
                </CardContent>
              </Card>
            )}

            {/* Settings Card */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Settings className="h-5 w-5" />
                  Setări
                </CardTitle>
                <CardDescription>
                  Configurează-ți contul și preferințele
                </CardDescription>
              </CardHeader>
              <CardContent>
                <div className="space-y-2">
                  <Link href="/settings/notifications">
                    <Button variant="ghost" size="sm" className="w-full justify-start">
                      Notificări
                    </Button>
                  </Link>
                  <Link href="/settings/privacy">
                    <Button variant="ghost" size="sm" className="w-full justify-start">
                      Confidențialitate
                    </Button>
                  </Link>
                  <Link href="/settings/security">
                    <Button variant="ghost" size="sm" className="w-full justify-start">
                      Securitate
                    </Button>
                  </Link>
                </div>
              </CardContent>
            </Card>
          </div>

          {/* Quick Actions */}
          <Card>
            <CardHeader>
              <CardTitle>Acțiuni rapide</CardTitle>
              <CardDescription>
                Funcționalități frecvent utilizate
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                <Link href="/properties">
                  <Button variant="outline" className="w-full">
                    Explorează proprietăți
                  </Button>
                </Link>
                
                {user.role === 'owner' && (
                  <Link href="/properties/add">
                    <Button variant="outline" className="w-full">
                      Adaugă proprietate
                    </Button>
                  </Link>
                )}
                
                <Link href="/support">
                  <Button variant="outline" className="w-full">
                    Suport
                  </Button>
                </Link>
                
                <Link href="/help">
                  <Button variant="outline" className="w-full">
                    Ajutor
                  </Button>
                </Link>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </Layout>
  )
}