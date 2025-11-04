import React from 'react';
import { CheckCircle, X } from 'lucide-react';

interface SuccessMessageProps {
  message: string;
  onClose?: () => void;
  duration?: number;
}

export const SuccessMessage: React.FC<SuccessMessageProps> = ({
  message,
  onClose,
  duration = 5000,
}) => {
  React.useEffect(() => {
    if (duration && onClose) {
      const timer = setTimeout(onClose, duration);
      return () => clearTimeout(timer);
    }
  }, [duration, onClose]);

  return (
    <div className="rounded-md bg-green-50 p-4 mb-4 animate-fade-in">
      <div className="flex">
        <CheckCircle className="h-5 w-5 text-green-400" />
        <div className="ml-3 flex-1">
          <p className="text-sm font-medium text-green-800">{message}</p>
        </div>
        {onClose && (
          <button
            onClick={onClose}
            className="ml-3 text-green-500 hover:text-green-600"
          >
            <X className="h-5 w-5" />
          </button>
        )}
      </div>
    </div>
  );
};

export const SuccessToast: React.FC<SuccessMessageProps> = ({
  message,
  onClose,
  duration = 3000,
}) => {
  React.useEffect(() => {
    if (duration && onClose) {
      const timer = setTimeout(onClose, duration);
      return () => clearTimeout(timer);
    }
  }, [duration, onClose]);

  return (
    <div className="fixed top-4 right-4 z-50 animate-slide-in-right">
      <div className="bg-white rounded-lg shadow-lg p-4 flex items-center space-x-3 min-w-[300px]">
        <CheckCircle className="h-6 w-6 text-green-500 flex-shrink-0" />
        <p className="text-sm font-medium text-gray-900 flex-1">{message}</p>
        {onClose && (
          <button
            onClick={onClose}
            className="text-gray-400 hover:text-gray-600"
          >
            <X className="h-5 w-5" />
          </button>
        )}
      </div>
    </div>
  );
};

export const SuccessModal: React.FC<{
  title: string;
  message: string;
  onClose: () => void;
  action?: {
    label: string;
    onClick: () => void;
  };
}> = ({ title, message, onClose, action }) => {
  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50">
      <div className="bg-white rounded-lg shadow-xl max-w-md w-full p-6 animate-scale-in">
        <div className="flex items-center justify-center w-12 h-12 mx-auto bg-green-100 rounded-full mb-4">
          <CheckCircle className="w-6 h-6 text-green-600" />
        </div>
        <h3 className="text-lg font-semibold text-center text-gray-900 mb-2">
          {title}
        </h3>
        <p className="text-sm text-center text-gray-600 mb-6">{message}</p>
        <div className="flex space-x-3">
          {action && (
            <button
              onClick={action.onClick}
              className="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              {action.label}
            </button>
          )}
          <button
            onClick={onClose}
            className="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  );
};

export const InlineSuccess: React.FC<{ message: string }> = ({ message }) => {
  return (
    <div className="flex items-center space-x-2 text-green-600">
      <CheckCircle className="w-5 h-5" />
      <span className="text-sm font-medium">{message}</span>
    </div>
  );
};
