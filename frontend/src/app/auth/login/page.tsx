export default function LoginPage() {
  return (
    <div className="min-h-screen bg-gray-50 flex items-center justify-center">
      <div className="max-w-md w-full">
        <div className="bg-white rounded-lg shadow-sm p-8">
          <h1 className="text-3xl font-bold mb-6 text-center">Sign In</h1>
          
          <div className="text-center py-12">
            <div className="text-6xl mb-4">🔐</div>
            <p className="text-gray-600 mb-6">
              Authentication system coming soon!
              <br />
              <br />
              For now, use the admin panel at:
            </p>
            <a
              href="http://localhost:8000/admin"
              target="_blank"
              rel="noopener noreferrer"
              className="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              Open Admin Panel
            </a>
          </div>
          
          <div className="mt-6 text-center">
            <a href="/" className="text-blue-600 hover:underline">
              ← Back to Home
            </a>
          </div>
        </div>
      </div>
    </div>
  )
}
