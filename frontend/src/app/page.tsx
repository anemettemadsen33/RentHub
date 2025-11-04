export default function Home() {
  return (
    <div className="min-h-screen bg-gradient-to-b from-blue-50 to-white">
      <div className="container mx-auto px-4 py-16">
        <div className="text-center">
          <h1 className="text-6xl font-bold text-gray-900 mb-4">
            Welcome to <span className="text-blue-600">RentHub</span>
          </h1>
          <p className="text-xl text-gray-600 mb-8">
            Your perfect property rental platform
          </p>
          
          <div className="flex justify-center gap-4 mb-16">
            <a
              href="/properties"
              className="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              Browse Properties
            </a>
            <a
              href="/auth/login"
              className="px-8 py-3 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors"
            >
              Sign In
            </a>
          </div>

          <div className="grid md:grid-cols-3 gap-8 max-w-4xl mx-auto">
            <div className="p-6 bg-white rounded-lg shadow-sm">
              <div className="text-4xl mb-4">🏠</div>
              <h3 className="text-xl font-semibold mb-2">Find Properties</h3>
              <p className="text-gray-600">
                Browse through thousands of verified rental properties
              </p>
            </div>

            <div className="p-6 bg-white rounded-lg shadow-sm">
              <div className="text-4xl mb-4">🔍</div>
              <h3 className="text-xl font-semibold mb-2">Easy Search</h3>
              <p className="text-gray-600">
                Filter by location, price, and amenities
              </p>
            </div>

            <div className="p-6 bg-white rounded-lg shadow-sm">
              <div className="text-4xl mb-4">⭐</div>
              <h3 className="text-xl font-semibold mb-2">Trusted Reviews</h3>
              <p className="text-gray-600">
                Read authentic reviews from real tenants
              </p>
            </div>
          </div>

          <div className="mt-16 p-6 bg-blue-50 rounded-lg max-w-2xl mx-auto">
            <h2 className="text-2xl font-bold mb-4">🎉 Setup Complete!</h2>
            <div className="text-left space-y-2">
              <p className="flex items-center gap-2">
                <span className="text-green-600">✓</span>
                <span>Backend API running on http://localhost:8000</span>
              </p>
              <p className="flex items-center gap-2">
                <span className="text-green-600">✓</span>
                <span>Frontend running on http://localhost:3000</span>
              </p>
              <p className="flex items-center gap-2">
                <span className="text-blue-600">→</span>
                <span>Admin Panel: http://localhost:8000/admin</span>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
