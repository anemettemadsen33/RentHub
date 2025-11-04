import '../src/styles/globals.css'
import { ThemeProvider } from '@/components/ThemeProvider'

export const metadata = {
  title: 'RentHub - Your Perfect Property Rental Platform',
  description: 'Find and rent your perfect property with RentHub',
}

export default function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <html lang="en" suppressHydrationWarning>
      <body>
        <ThemeProvider defaultTheme="system" storageKey="renthub-ui-theme">
          {children}
        </ThemeProvider>
      </body>
    </html>
  )
}
