export default function OfflinePage() {
  return (
    <div className="flex min-h-screen items-center justify-center flex-col gap-4 p-8 text-center">
      <h1 className="text-2xl font-bold">You are offline</h1>
      <p className="text-muted-foreground max-w-md">Some features may be unavailable. Please check your internet connection and retry.</p>
    </div>
  );
}
