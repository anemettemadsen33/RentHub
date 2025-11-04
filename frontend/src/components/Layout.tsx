'use client'

import { ReactNode, useState } from 'react'
import Link from 'next/link'
import { Button } from '@/components/ui/button'
import { useAuth } from '@/hooks/useAuth'
import { Menu, X, User, LogOut, Heart, Calendar, Home } from 'lucide-react'
import { ThemeToggle } from '@/components/ThemeToggle'
import { Sheet, SheetContent, SheetTrigger } from '@/components/ui/sheet'
import { Separator } from '@/components/ui/separator'

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
      <header className="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur-sm supports-[backdrop-filter]:bg-background/80">
        <nav className="container mx-auto px-4">
          <div className="flex justify-between items-center h-16">
            {/* Logo */}
            <Link href="/" className="flex items-center space-x-2">
              <Home className="h-6 w-6 text-primary" />
              <span className="text-2xl font-bold bg-gradient-to-r from-primary to-blue-600 bg-clip-text text-transparent">
                RentHub
              </span>
            </Link>

            {/* Desktop Navigation */}
            <div className="hidden md:flex items-center space-x-6">
              <Link href="/properties" className="text-sm font-medium text-muted-foreground transition-colors hover:text-primary">
                Proprietăți
              </Link>
              <Link href="/about" className="text-sm font-medium text-muted-foreground transition-colors hover:text-primary">
                Despre noi
              </Link>
              <Link href="/contact" className="text-sm font-medium text-muted-foreground transition-colors hover:text-primary">
                Contact
              </Link>
            </div>

            {/* User Menu */}
            <div className="hidden md:flex items-center space-x-4">
              <ThemeToggle />
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

            {/* Mobile menu */}
            <Sheet open={isMobileMenuOpen} onOpenChange={setIsMobileMenuOpen}>
              <SheetTrigger asChild className="md:hidden">
                <Button variant="ghost" size="icon">
                  <Menu className="h-5 w-5" />
                </Button>
              </SheetTrigger>
              <SheetContent side="right">
                <div className="flex flex-col space-y-4 mt-8">
                  <Link 
                    href="/properties" 
                    className="text-lg font-medium"
                    onClick={() => setIsMobileMenuOpen(false)}
                  >
                    Proprietăți
                  </Link>
                  <Link 
                    href="/about" 
                    className="text-lg font-medium"
                    onClick={() => setIsMobileMenuOpen(false)}
                  >
                    Despre noi
                  </Link>
                  <Link 
                    href="/contact" 
                    className="text-lg font-medium"
                    onClick={() => setIsMobileMenuOpen(false)}
                  >
                    Contact
                  </Link>
                  
                  {user ? (
                    <>
                      <Separator />
                      <Link 
                        href="/dashboard"
                        className="text-lg font-medium"
                        onClick={() => setIsMobileMenuOpen(false)}
                      >
                        Dashboard
                      </Link>
                      <Link 
                        href="/favorites"
                        className="text-lg font-medium"
                        onClick={() => setIsMobileMenuOpen(false)}
                      >
                        Favorite
                      </Link>
                      <Link 
                        href="/bookings"
                        className="text-lg font-medium"
                        onClick={() => setIsMobileMenuOpen(false)}
                      >
                        Rezervările mele
                      </Link>
                      <Button
                        onClick={handleLogout}
                        variant="outline"
                        className="w-full justify-start"
                      >
                        <LogOut className="mr-2 h-4 w-4" />
                        Deconectare
                      </Button>
                    </>
                  ) : (
                    <div className="pt-4 border-t flex flex-col space-y-2">
                      <Link 
                        href="/auth/login"
                        onClick={() => setIsMobileMenuOpen(false)}
                      >
                        <Button variant="outline" className="w-full">Conectare</Button>
                      </Link>
                      <Link 
                        href="/auth/register"
                        onClick={() => setIsMobileMenuOpen(false)}
                      >
                        <Button className="w-full">Înregistrare</Button>
                      </Link>
                    </div>
                  )}
                </div>
              </SheetContent>
            </Sheet>
          </div>
        </nav>
      </header>

      {/* Main Content */}
      <main className="flex-1">
        {children}
      </main>

      {/* Footer */}
      <footer className="border-t bg-muted/50">
        <div className="container mx-auto px-4 py-12">
          <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
            {/* Company Info */}
            <div className="space-y-4">
              <div className="flex items-center space-x-2">
                <Home className="h-6 w-6 text-primary" />
                <span className="text-xl font-bold bg-gradient-to-r from-primary to-blue-600 bg-clip-text text-transparent">
                  RentHub
                </span>
              </div>
              <p className="text-muted-foreground text-sm leading-relaxed">
                Platforma ta de încredere pentru găsirea și închirierea proprietăților în România.
              </p>
            </div>

            {/* Quick Links */}
            <div>
              <h3 className="font-semibold mb-4">Link-uri rapide</h3>
              <div className="space-y-2 text-sm">
                <Link href="/properties" className="block text-muted-foreground hover:text-primary transition-colors">
                  Proprietăți
                </Link>
                <Link href="/about" className="block text-muted-foreground hover:text-primary transition-colors">
                  Despre noi
                </Link>
                <Link href="/contact" className="block text-muted-foreground hover:text-primary transition-colors">
                  Contact
                </Link>
                <Link href="/faq" className="block text-muted-foreground hover:text-primary transition-colors">
                  Întrebări frecvente
                </Link>
              </div>
            </div>

            {/* Support */}
            <div>
              <h3 className="font-semibold mb-4">Suport</h3>
              <div className="space-y-2 text-sm">
                <Link href="/help" className="block text-muted-foreground hover:text-primary transition-colors">
                  Centru de ajutor
                </Link>
                <Link href="/terms" className="block text-muted-foreground hover:text-primary transition-colors">
                  Termeni și condiții
                </Link>
                <Link href="/privacy" className="block text-muted-foreground hover:text-primary transition-colors">
                  Politica de confidențialitate
                </Link>
              </div>
            </div>

            {/* Contact Info */}
            <div>
              <h3 className="font-semibold mb-4">Contact</h3>
              <div className="space-y-2 text-sm text-muted-foreground">
                <p>Email: contact@renthub.ro</p>
                <p>Telefon: +40 123 456 789</p>
                <p>Adresa: București, România</p>
              </div>
            </div>
          </div>

          <Separator className="my-8" />
          
          <div className="flex flex-col md:flex-row justify-between items-center">
            <p className="text-muted-foreground text-sm">
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