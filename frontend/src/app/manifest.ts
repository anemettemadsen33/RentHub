export default function manifest() {
  return {
    name: 'RentHub',
    short_name: 'RentHub',
    description: 'Modern property rental platform',
    start_url: '/',
    display: 'standalone',
    background_color: '#ffffff',
    theme_color: '#3b82f6',
    icons: [
      {
        src: '/favicon.ico',
        sizes: 'any',
        type: 'image/x-icon',
      },
    ],
  }
}
