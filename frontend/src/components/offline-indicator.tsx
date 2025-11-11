"use client";
import { useEffect, useState } from 'react';
import { WifiOff, Wifi } from 'lucide-react';

export function OfflineIndicator() {
  const [online, setOnline] = useState<boolean>(true);
  const [show, setShow] = useState(false);

  useEffect(() => {
    const update = () => {
      const status = navigator.onLine;
      setOnline(status);
      setShow(true);
      if (status) {
        // Hide banner shortly after coming back online
        setTimeout(() => setShow(false), 2500);
      }
    };
    update();
    window.addEventListener('online', update);
    window.addEventListener('offline', update);
    return () => {
      window.removeEventListener('online', update);
      window.removeEventListener('offline', update);
    };
  }, []);

  if (!show) return null;

  return (
    <div
      className={`fixed top-0 left-0 right-0 z-50 flex items-center justify-center px-4 py-2 text-sm font-medium transition-transform ${online ? 'bg-green-600 text-white' : 'bg-amber-600 text-white'} animate-slideDown`}
      role="status"
      aria-live="polite"
    >
      {online ? (
        <span className="flex items-center gap-2"><Wifi className="h-4 w-4" /> Back online</span>
      ) : (
        <span className="flex items-center gap-2"><WifiOff className="h-4 w-4" /> Offline – showing cached data</span>
      )}
    </div>
  );
}
