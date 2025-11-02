import { useState, useEffect } from 'react'
import { useRouter } from 'next/router'
import Link from 'next/link'
import Head from 'next/head'
import { Layout } from '@/components/Layout'
import { Button } from '@/components/ui/Button'
import { Input } from '@/components/ui/Input'
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/Card'
import { useAuth } from '@/hooks/useAuth'
import { Mail, Lock, User, Eye, EyeOff } from 'lucide-react'

export default function RegisterPage() {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'guest' as 'guest' | 'owner'
  })
  const [showPassword, setShowPassword] = useState(false)
  const [showConfirmPassword, setShowConfirmPassword] = useState(false)
  const [errors, setErrors] = useState<{[key: string]: string}>({})
  
  const { register, isLoading, user } = useAuth()
  const router = useRouter()

  useEffect(() => {
    if (user) {
      router.push('/dashboard')
    }
  }, [user, router])

  const handleInputChange = (field: string, value: string) => {
    setFormData(prev => ({ ...prev, [field]: value }))
  }

  const validateForm = () => {
    const newErrors: {[key: string]: string} = {}

    if (!formData.name) {
      newErrors.name = 'Numele este obligatoriu'
    }

    if (!formData.email) {
      newErrors.email = 'Email-ul este obligatoriu'
    } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
      newErrors.email = 'Email-ul nu este valid'
    }

    if (!formData.password) {
      newErrors.password = 'Parola este obligatorie'
    } else if (formData.password.length < 8) {
      newErrors.password = 'Parola trebuie să aibă cel puțin 8 caractere'
    }

    if (!formData.password_confirmation) {
      newErrors.password_confirmation = 'Confirmarea parolei este obligatorie'
    } else if (formData.password !== formData.password_confirmation) {
      newErrors.password_confirmation = 'Parolele nu se potrivesc'
    }

    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    
    if (!validateForm()) return

    try {
      await register(formData)
      router.push('/dashboard')
    } catch (error: any) {
      if (error.response?.status === 422) {
        setErrors(error.response.data.errors || {})
      } else {
        setErrors({ general: 'A apărut o eroare. Te rugăm să încerci din nou.' })
      }
    }
  }

  return (
    <Layout>
      <Head>
        <title>Înregistrare - RentHub</title>
        <meta name="description" content="Creează-ți un cont RentHub gratuit" />
      </Head>

      <div className="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div className="max-w-md w-full">
          <Card>
            <CardHeader className="text-center">
              <CardTitle className="text-2xl font-bold">Creează un cont</CardTitle>
              <CardDescription>
                Înregistrează-te pentru a avea acces la toate funcționalitățile
              </CardDescription>
            </CardHeader>

            <CardContent>
              <form onSubmit={handleSubmit} className="space-y-4">
                {errors.general && (
                  <div className="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-md">
                    {errors.general}
                  </div>
                )}

                <Input
                  label="Numele complet"
                  type="text"
                  value={formData.name}
                  onChange={(e) => handleInputChange('name', e.target.value)}
                  placeholder="Ex: Ion Popescu"
                  error={errors.name}
                  icon={<User className="h-4 w-4 text-gray-400" />}
                />

                <Input
                  label="Email"
                  type="email"
                  value={formData.email}
                  onChange={(e) => handleInputChange('email', e.target.value)}
                  placeholder="exemplu@email.com"
                  error={errors.email}
                  icon={<Mail className="h-4 w-4 text-gray-400" />}
                />

                <div className="relative">
                  <Input
                    label="Parola"
                    type={showPassword ? 'text' : 'password'}
                    value={formData.password}
                    onChange={(e) => handleInputChange('password', e.target.value)}
                    placeholder="Minim 8 caractere"
                    error={errors.password}
                    icon={<Lock className="h-4 w-4 text-gray-400" />}
                  />
                  <button
                    type="button"
                    className="absolute right-3 top-8 text-gray-400 hover:text-gray-600"
                    onClick={() => setShowPassword(!showPassword)}
                  >
                    {showPassword ? (
                      <EyeOff className="h-4 w-4" />
                    ) : (
                      <Eye className="h-4 w-4" />
                    )}
                  </button>
                </div>

                <div className="relative">
                  <Input
                    label="Confirmă parola"
                    type={showConfirmPassword ? 'text' : 'password'}
                    value={formData.password_confirmation}
                    onChange={(e) => handleInputChange('password_confirmation', e.target.value)}
                    placeholder="Repetă parola"
                    error={errors.password_confirmation}
                    icon={<Lock className="h-4 w-4 text-gray-400" />}
                  />
                  <button
                    type="button"
                    className="absolute right-3 top-8 text-gray-400 hover:text-gray-600"
                    onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                  >
                    {showConfirmPassword ? (
                      <EyeOff className="h-4 w-4" />
                    ) : (
                      <Eye className="h-4 w-4" />
                    )}
                  </button>
                </div>

                <div>
                  <label className="text-sm font-medium text-gray-700 mb-2 block">
                    Tipul contului
                  </label>
                  <div className="grid grid-cols-2 gap-3">
                    <button
                      type="button"
                      onClick={() => handleInputChange('role', 'guest')}
                      className={`p-3 border rounded-md text-sm font-medium transition-colors ${
                        formData.role === 'guest'
                          ? 'border-blue-500 bg-blue-50 text-blue-700'
                          : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'
                      }`}
                    >
                      Oaspete
                    </button>
                    <button
                      type="button"
                      onClick={() => handleInputChange('role', 'owner')}
                      className={`p-3 border rounded-md text-sm font-medium transition-colors ${
                        formData.role === 'owner'
                          ? 'border-blue-500 bg-blue-50 text-blue-700'
                          : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'
                      }`}
                    >
                      Proprietar
                    </button>
                  </div>
                </div>

                <Button
                  type="submit"
                  className="w-full"
                  disabled={isLoading}
                >
                  {isLoading ? 'Se înregistrează...' : 'Creează contul'}
                </Button>
              </form>
            </CardContent>

            <CardFooter className="text-center">
              <div className="text-sm text-gray-600">
                Ai deja cont?{' '}
                <Link href="/auth/login" className="text-blue-600 hover:text-blue-500 font-medium">
                  Conectează-te aici
                </Link>
              </div>
            </CardFooter>
          </Card>
        </div>
      </div>
    </Layout>
  )
}