import { ReactNode, useState } from 'react'
import Link from 'next/link'
import { Button } from '@/components/ui/Button'
import { useAuth } from '@/hooks/useAuth'
import { Menu, X, User, LogOut, Heart, Calendar, Home } from 'lucide-react'

interface LayoutProps {
  children: ReactNode
}

export const Layout = ({ children }: LayoutProps) => {
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false)
  const { user, logout } = useAuth()

  const handleLogout = () => {
    logout()
    setIsMobileMenuOpen(false)
  }

  return (
    <div className="min-h-screen flex flex-col">
      {/* Header */}
      <header className="bg-white shadow-sm border-b">
        <nav className="container mx-auto px-4">
          <div className="flex justify-between items-center h-16">
            {/* Logo */}
            <Link href="/" className="flex items-center space-x-2">
              <Home className="h-8 w-8 text-blue-600" />
              <span className="text-2xl font-bold text-gray-900">RentHub</span>
            </Link>

            {/* Desktop Navigation */}
            <div className="hidden md:flex items-center space-x-8">
              <Link href="/properties" className="text-gray-700 hover:text-blue-600 transition-colors">
                Proprietăți
              </Link>
              <Link href="/about" className="text-gray-700 hover:text-blue-600 transition-colors">
                Despre noi
              </Link>
              <Link href="/contact" className="text-gray-700 hover:text-blue-600 transition-colors">
                Contact
              </Link>
            </div>

            {/* User Menu */}
            <div className="hidden md:flex items-center space-x-4">
              {user ? (
                <div className="flex items-center space-x-4">
                  <Link href="/dashboard">
                    <Button variant="ghost" size="sm">
                      <User className="h-4 w-4 mr-2" />
                      {user.name}
                    </Button>
                  </Link>
                  <Link href="/favorites">
                    <Button variant="ghost" size="sm">
                      <Heart className="h-4 w-4" />
                    </Button>
                  </Link>
                  <Link href="/bookings">
                    <Button variant="ghost" size="sm">
                      <Calendar className="h-4 w-4" />
                    </Button>
                  </Link>
                  <Button variant="ghost" size="sm" onClick={handleLogout}>
                    <LogOut className="h-4 w-4" />
                  </Button>
                </div>
              ) : (
                <div className="flex items-center space-x-2">
                  <Link href="/auth/login">
                    <Button variant="ghost">Conectare</Button>
                  </Link>
                  <Link href="/auth/register">
                    <Button>Înregistrare</Button>
                  </Link>
                </div>
              )}
            </div>

            {/* Mobile menu button */}
            <button
              className="md:hidden"
              onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
            >
              {isMobileMenuOpen ? (
                <X className="h-6 w-6 text-gray-700" />
              ) : (
                <Menu className="h-6 w-6 text-gray-700" />
              )}
            </button>
          </div>

          {/* Mobile Navigation */}
          {isMobileMenuOpen && (
            <div className="md:hidden py-4 border-t">
              <div className="flex flex-col space-y-4">
                <Link 
                  href="/properties" 
                  className="text-gray-700 hover:text-blue-600 transition-colors"
                  onClick={() => setIsMobileMenuOpen(false)}
                >
                  Proprietăți
                </Link>
                <Link 
                  href="/about" 
                  className="text-gray-700 hover:text-blue-600 transition-colors"
                  onClick={() => setIsMobileMenuOpen(false)}
                >
                  Despre noi
                </Link>
                <Link 
                  href="/contact" 
                  className="text-gray-700 hover:text-blue-600 transition-colors"
                  onClick={() => setIsMobileMenuOpen(false)}
                >
                  Contact
                </Link>
                
                {user ? (
                  <>
                    <hr className="border-gray-200" />
                    <Link 
                      href="/dashboard"
                      className="text-gray-700 hover:text-blue-600 transition-colors"
                      onClick={() => setIsMobileMenuOpen(false)}
                    >
                      Dashboard
                    </Link>
                    <Link 
                      href="/favorites"
                      className="text-gray-700 hover:text-blue-600 transition-colors"
                      onClick={() => setIsMobileMenuOpen(false)}
                    >
                      Favorite
                    </Link>
                    <Link 
                      href="/bookings"
                      className="text-gray-700 hover:text-blue-600 transition-colors"
                      onClick={() => setIsMobileMenuOpen(false)}
                    >
                      Rezervările mele
                    </Link>
                    <button
                      onClick={handleLogout}
                      className="text-left text-gray-700 hover:text-blue-600 transition-colors"
                    >
                      Deconectare
                    </button>
                  </>
                ) : (
                  <>
                    <hr className="border-gray-200" />
                    <Link 
                      href="/auth/login"
                      onClick={() => setIsMobileMenuOpen(false)}
                    >
                      <Button variant="ghost" className="w-full justify-start">
                        Conectare
                      </Button>
                    </Link>
                    <Link 
                      href="/auth/register"
                      onClick={() => setIsMobileMenuOpen(false)}
                    >
                      <Button className="w-full">Înregistrare</Button>
                    </Link>
                  </>
                )}
              </div>
            </div>
          )}
        </nav>
      </header>

      {/* Main Content */}
      <main className="flex-1">
        {children}
      </main>

      {/* Footer */}
      <footer className="bg-gray-900 text-white">
        <div className="container mx-auto px-4 py-12">
          <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
            {/* Company Info */}
            <div className="space-y-4">
              <div className="flex items-center space-x-2">
                <Home className="h-6 w-6 text-blue-400" />
                <span className="text-xl font-bold">RentHub</span>
              </div>
              <p className="text-gray-300 text-sm">
                Platforma ta de încredere pentru găsirea și închirierea proprietăților în România.
              </p>
            </div>

            {/* Quick Links */}
            <div>
              <h3 className="font-semibold mb-4">Link-uri rapide</h3>
              <div className="space-y-2 text-sm">
                <Link href="/properties" className="block text-gray-300 hover:text-white transition-colors">
                  Proprietăți
                </Link>
                <Link href="/about" className="block text-gray-300 hover:text-white transition-colors">
                  Despre noi
                </Link>
                <Link href="/contact" className="block text-gray-300 hover:text-white transition-colors">
                  Contact
                </Link>
                <Link href="/faq" className="block text-gray-300 hover:text-white transition-colors">
                  Întrebări frecvente
                </Link>
              </div>
            </div>

            {/* Support */}
            <div>
              <h3 className="font-semibold mb-4">Suport</h3>
              <div className="space-y-2 text-sm">
                <Link href="/help" className="block text-gray-300 hover:text-white transition-colors">
                  Centru de ajutor
                </Link>
                <Link href="/terms" className="block text-gray-300 hover:text-white transition-colors">
                  Termeni și condiții
                </Link>
                <Link href="/privacy" className="block text-gray-300 hover:text-white transition-colors">
                  Politica de confidențialitate
                </Link>
              </div>
            </div>

            {/* Contact Info */}
            <div>
              <h3 className="font-semibold mb-4">Contact</h3>
              <div className="space-y-2 text-sm text-gray-300">
                <p>Email: contact@renthub.ro</p>
                <p>Telefon: +40 123 456 789</p>
                <p>Adresa: București, România</p>
              </div>
            </div>
          </div>

          <hr className="border-gray-700 my-8" />
          
          <div className="flex flex-col md:flex-row justify-between items-center">
            <p className="text-gray-300 text-sm">
              © 2024 RentHub. Toate drepturile rezervate.
            </p>
            <div className="flex space-x-4 mt-4 md:mt-0">
              {/* Social Media Icons can be added here */}
            </div>
          </div>
        </div>
      </footer>
    </div>
  )
}