import { useState, useEffect } from 'react'
import { useRouter } from 'next/router'
import Link from 'next/link'
import Head from 'next/head'
import { Layout } from '@/components/Layout'
import { Button } from '@/components/ui/Button'
import { Input } from '@/components/ui/Input'
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/Card'
import { useAuth } from '@/hooks/useAuth'
import { Mail, Lock, Eye, EyeOff } from 'lucide-react'

export default function LoginPage() {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [showPassword, setShowPassword] = useState(false)
  const [errors, setErrors] = useState<{[key: string]: string}>({})
  
  const { login, isLoading, user } = useAuth()
  const router = useRouter()

  useEffect(() => {
    if (user) {
      router.push('/dashboard')
    }
  }, [user, router])

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setErrors({})

    if (!email) {
      setErrors(prev => ({ ...prev, email: 'Email-ul este obligatoriu' }))
      return
    }

    if (!password) {
      setErrors(prev => ({ ...prev, password: 'Parola este obligatorie' }))
      return
    }

    try {
      await login({ email, password })
      router.push('/dashboard')
    } catch (error: any) {
      if (error.response?.status === 422) {
        setErrors(error.response.data.errors || {})
      } else if (error.response?.status === 401) {
        setErrors({ general: 'Email sau parolă incorectă' })
      } else {
        setErrors({ general: 'A apărut o eroare. Te rugăm să încerci din nou.' })
      }
    }
  }

  return (
    <Layout>
      <Head>
        <title>Conectare - RentHub</title>
        <meta name="description" content="Conectează-te la contul tău RentHub" />
      </Head>

      <div className="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div className="max-w-md w-full">
          <Card>
            <CardHeader className="text-center">
              <CardTitle className="text-2xl font-bold">Bun venit înapoi!</CardTitle>
              <CardDescription>
                Conectează-te la contul tău pentru a continua
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
                  label="Email"
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="exemplu@email.com"
                  error={errors.email}
                  icon={<Mail className="h-4 w-4 text-gray-400" />}
                />

                <div className="relative">
                  <Input
                    label="Parola"
                    type={showPassword ? 'text' : 'password'}
                    value={password}
                    onChange={(e) => setPassword(e.target.value)}
                    placeholder="Introdu parola"
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

                <Button
                  type="submit"
                  className="w-full"
                  disabled={isLoading}
                >
                  {isLoading ? 'Se conectează...' : 'Conectare'}
                </Button>
              </form>
            </CardContent>

            <CardFooter className="flex flex-col space-y-4">
              <div className="text-center text-sm text-gray-600">
                <Link href="/auth/forgot-password" className="text-blue-600 hover:text-blue-500">
                  Ai uitat parola?
                </Link>
              </div>
              
              <div className="text-center text-sm text-gray-600">
                Nu ai cont?{' '}
                <Link href="/auth/register" className="text-blue-600 hover:text-blue-500 font-medium">
                  Înregistrează-te aici
                </Link>
              </div>
            </CardFooter>
          </Card>
        </div>
      </div>
    </Layout>
  )
}