import React, { useEffect, useState } from 'react';

type ToastType = 'success' | 'error' | 'warning' | 'info';

interface ToastProps {
  message: string;
  type?: ToastType;
  duration?: number;
  onClose?: () => void;
}

export const Toast: React.FC<ToastProps> = ({
  message,
  type = 'info',
  duration = 5000,
  onClose
}) => {
  const [isVisible, setIsVisible] = useState(true);
  const [isExiting, setIsExiting] = useState(false);

  useEffect(() => {
    if (duration > 0) {
      const timer = setTimeout(() => {
        handleClose();
      }, duration);

      return () => clearTimeout(timer);
    }
  }, [duration]);

  const handleClose = () => {
    setIsExiting(true);
    setTimeout(() => {
      setIsVisible(false);
      onClose?.();
    }, 300);
  };

  if (!isVisible) return null;

  const typeStyles = {
    success: 'bg-green-500 text-white',
    error: 'bg-red-500 text-white',
    warning: 'bg-yellow-500 text-white',
    info: 'bg-blue-500 text-white'
  };

  const icons = {
    success: '✓',
    error: '✕',
    warning: '⚠',
    info: 'ℹ'
  };

  return (
    <div
      className={`
        fixed top-4 right-4 z-50 max-w-md rounded-lg shadow-lg p-4 flex items-center gap-3
        ${typeStyles[type]}
        ${isExiting ? 'animate-slide-out-right' : 'animate-slide-in-right'}
      `}
    >
      <span className="text-xl">{icons[type]}</span>
      <p className="flex-1">{message}</p>
      <button
        onClick={handleClose}
        className="ml-2 hover:opacity-80 transition-opacity"
        aria-label="Close"
      >
        ✕
      </button>
    </div>
  );
};

// Toast manager for programmatic usage
class ToastManager {
  private listeners: ((toast: ToastProps & { id: string }) => void)[] = [];

  subscribe(listener: (toast: ToastProps & { id: string }) => void) {
    this.listeners.push(listener);
    return () => {
      this.listeners = this.listeners.filter(l => l !== listener);
    };
  }

  private notify(toast: ToastProps) {
    const id = Math.random().toString(36).substr(2, 9);
    this.listeners.forEach(listener => listener({ ...toast, id }));
  }

  success(message: string, duration?: number) {
    this.notify({ message, type: 'success', duration });
  }

  error(message: string, duration?: number) {
    this.notify({ message, type: 'error', duration });
  }

  warning(message: string, duration?: number) {
    this.notify({ message, type: 'warning', duration });
  }

  info(message: string, duration?: number) {
    this.notify({ message, type: 'info', duration });
  }
}

export const toast = new ToastManager();

export const ToastContainer: React.FC = () => {
  const [toasts, setToasts] = useState<(ToastProps & { id: string })[]>([]);

  useEffect(() => {
    return toast.subscribe((newToast) => {
      setToasts(prev => [...prev, newToast]);
    });
  }, []);

  const removeToast = (id: string) => {
    setToasts(prev => prev.filter(t => t.id !== id));
  };

  return (
    <>
      {toasts.map(({ id, ...toastProps }) => (
        <Toast
          key={id}
          {...toastProps}
          onClose={() => removeToast(id)}
        />
      ))}
    </>
  );
};
