import React from 'react';
import { AlertCircle, CheckCircle, Info, XCircle } from 'lucide-react';

// Error State Component
export const ErrorState: React.FC<{ 
  title?: string; 
  message?: string; 
  onRetry?: () => void;
}> = ({ 
  title = 'Something went wrong', 
  message = 'We encountered an error. Please try again.',
  onRetry 
}) => {
  return (
    <div className="flex flex-col items-center justify-center p-8 text-center">
      <XCircle className="w-16 h-16 text-red-500 mb-4" />
      <h3 className="text-xl font-semibold text-gray-900 mb-2">{title}</h3>
      <p className="text-gray-600 mb-6 max-w-md">{message}</p>
      {onRetry && (
        <button
          onClick={onRetry}
          className="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
          Try Again
        </button>
      )}
    </div>
  );
};

// Empty State Component
export const EmptyState: React.FC<{ 
  title?: string; 
  message?: string;
  action?: { label: string; onClick: () => void };
  icon?: React.ReactNode;
}> = ({ 
  title = 'No items found', 
  message = 'Get started by creating your first item.',
  action,
  icon 
}) => {
  return (
    <div className="flex flex-col items-center justify-center p-12 text-center">
      {icon || <Info className="w-16 h-16 text-gray-400 mb-4" />}
      <h3 className="text-xl font-semibold text-gray-900 mb-2">{title}</h3>
      <p className="text-gray-600 mb-6 max-w-md">{message}</p>
      {action && (
        <button
          onClick={action.onClick}
          className="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
          {action.label}
        </button>
      )}
    </div>
  );
};

// Success Message Component
export const SuccessMessage: React.FC<{ 
  message: string; 
  onClose?: () => void;
  autoClose?: boolean;
}> = ({ 
  message, 
  onClose,
  autoClose = true 
}) => {
  React.useEffect(() => {
    if (autoClose && onClose) {
      const timer = setTimeout(onClose, 5000);
      return () => clearTimeout(timer);
    }
  }, [autoClose, onClose]);

  return (
    <div className="fixed top-4 right-4 z-50 animate-slide-in-right">
      <div className="flex items-center bg-green-50 border border-green-200 rounded-lg p-4 shadow-lg">
        <CheckCircle className="w-5 h-5 text-green-600 mr-3" />
        <p className="text-green-800 font-medium">{message}</p>
        {onClose && (
          <button
            onClick={onClose}
            className="ml-4 text-green-600 hover:text-green-800"
          >
            ×
          </button>
        )}
      </div>
    </div>
  );
};

// Alert Component
export const Alert: React.FC<{
  type: 'info' | 'warning' | 'error' | 'success';
  title?: string;
  message: string;
  onClose?: () => void;
}> = ({ type, title, message, onClose }) => {
  const styles = {
    info: {
      bg: 'bg-blue-50',
      border: 'border-blue-200',
      icon: <Info className="w-5 h-5 text-blue-600" />,
      text: 'text-blue-800',
      title: 'text-blue-900'
    },
    warning: {
      bg: 'bg-yellow-50',
      border: 'border-yellow-200',
      icon: <AlertCircle className="w-5 h-5 text-yellow-600" />,
      text: 'text-yellow-800',
      title: 'text-yellow-900'
    },
    error: {
      bg: 'bg-red-50',
      border: 'border-red-200',
      icon: <XCircle className="w-5 h-5 text-red-600" />,
      text: 'text-red-800',
      title: 'text-red-900'
    },
    success: {
      bg: 'bg-green-50',
      border: 'border-green-200',
      icon: <CheckCircle className="w-5 h-5 text-green-600" />,
      text: 'text-green-800',
      title: 'text-green-900'
    }
  };

  const style = styles[type];

  return (
    <div className={`${style.bg} border ${style.border} rounded-lg p-4`}>
      <div className="flex items-start">
        <div className="flex-shrink-0">{style.icon}</div>
        <div className="ml-3 flex-1">
          {title && <h4 className={`font-semibold ${style.title} mb-1`}>{title}</h4>}
          <p className={style.text}>{message}</p>
        </div>
        {onClose && (
          <button
            onClick={onClose}
            className={`ml-3 ${style.text} hover:opacity-70`}
          >
            ×
          </button>
        )}
      </div>
    </div>
  );
};

// Toast Notification
export const Toast: React.FC<{
  message: string;
  type?: 'success' | 'error' | 'info';
  duration?: number;
}> = ({ message, type = 'info', duration = 3000 }) => {
  const [visible, setVisible] = React.useState(true);

  React.useEffect(() => {
    const timer = setTimeout(() => setVisible(false), duration);
    return () => clearTimeout(timer);
  }, [duration]);

  if (!visible) return null;

  const colors = {
    success: 'bg-green-600',
    error: 'bg-red-600',
    info: 'bg-blue-600'
  };

  return (
    <div className={`fixed bottom-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-slide-in-up`}>
      {message}
    </div>
  );
};

// Confirmation Dialog
export const ConfirmDialog: React.FC<{
  title: string;
  message: string;
  onConfirm: () => void;
  onCancel: () => void;
  confirmLabel?: string;
  cancelLabel?: string;
  danger?: boolean;
}> = ({
  title,
  message,
  onConfirm,
  onCancel,
  confirmLabel = 'Confirm',
  cancelLabel = 'Cancel',
  danger = false
}) => {
  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div className="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 className="text-xl font-semibold text-gray-900 mb-4">{title}</h3>
        <p className="text-gray-600 mb-6">{message}</p>
        <div className="flex space-x-3 justify-end">
          <button
            onClick={onCancel}
            className="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            {cancelLabel}
          </button>
          <button
            onClick={onConfirm}
            className={`px-4 py-2 rounded-lg text-white transition-colors ${
              danger 
                ? 'bg-red-600 hover:bg-red-700' 
                : 'bg-blue-600 hover:bg-blue-700'
            }`}
          >
            {confirmLabel}
          </button>
        </div>
      </div>
    </div>
  );
};
