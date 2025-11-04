// RentHub Design System Components
// Complete UI Component Library

import React, { useEffect, useRef, useState } from 'react';
import classNames from 'classnames';

// ============================================
// Button Component
// ============================================
export const Button = ({ 
    children, 
    variant = 'primary', 
    size = 'md',
    loading = false,
    disabled = false,
    onClick,
    className = '',
    ...props 
}) => {
    const baseStyles = 'inline-flex items-center justify-center font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
    
    const variants = {
        primary: 'bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500 active:bg-primary-800',
        secondary: 'bg-gray-200 text-gray-900 hover:bg-gray-300 focus:ring-gray-500',
        outline: 'border-2 border-primary-600 text-primary-600 hover:bg-primary-50 focus:ring-primary-500',
        ghost: 'text-gray-700 hover:bg-gray-100 focus:ring-gray-500',
        danger: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500 active:bg-red-800',
        success: 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-500',
    };

    const sizes = {
        xs: 'px-2 py-1 text-xs rounded',
        sm: 'px-3 py-1.5 text-sm rounded-md',
        md: 'px-4 py-2 text-base rounded-lg',
        lg: 'px-6 py-3 text-lg rounded-xl',
        xl: 'px-8 py-4 text-xl rounded-2xl',
    };

    const classes = classNames(
        baseStyles,
        variants[variant],
        sizes[size],
        {
            'cursor-wait': loading,
        },
        className
    );

    return (
        <button 
            className={classes}
            disabled={disabled || loading}
            onClick={onClick}
            {...props}
        >
            {loading && (
                <svg className="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                </svg>
            )}
            {children}
        </button>
    );
};

// ============================================
// Card Component
// ============================================
export const Card = ({ children, hover = false, className = '', ...props }) => {
    return (
        <div 
            className={classNames(
                'bg-white rounded-xl shadow-md overflow-hidden transition-all duration-200',
                {
                    'hover:shadow-xl hover:-translate-y-1 cursor-pointer': hover,
                },
                className
            )}
            {...props}
        >
            {children}
        </div>
    );
};

export const CardHeader = ({ children, className = '' }) => (
    <div className={classNames('px-6 py-4 border-b border-gray-200', className)}>
        {children}
    </div>
);

export const CardBody = ({ children, className = '' }) => (
    <div className={classNames('px-6 py-4', className)}>
        {children}
    </div>
);

export const CardFooter = ({ children, className = '' }) => (
    <div className={classNames('px-6 py-4 border-t border-gray-200 bg-gray-50', className)}>
        {children}
    </div>
);

// ============================================
// Input Component
// ============================================
export const Input = ({ 
    label, 
    error, 
    helperText,
    icon,
    className = '',
    containerClassName = '',
    ...props 
}) => {
    return (
        <div className={classNames('space-y-1', containerClassName)}>
            {label && (
                <label className="block text-sm font-medium text-gray-700">
                    {label}
                    {props.required && <span className="text-red-500 ml-1">*</span>}
                </label>
            )}
            <div className="relative">
                {icon && (
                    <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        {icon}
                    </div>
                )}
                <input
                    className={classNames(
                        'w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 transition-all',
                        {
                            'border-gray-300 focus:ring-primary-500 focus:border-primary-500': !error,
                            'border-red-500 focus:ring-red-500 focus:border-red-500': error,
                            'pl-10': icon,
                        },
                        className
                    )}
                    {...props}
                />
            </div>
            {error && (
                <p className="text-sm text-red-600 flex items-center">
                    <svg className="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" />
                    </svg>
                    {error}
                </p>
            )}
            {helperText && !error && (
                <p className="text-sm text-gray-500">{helperText}</p>
            )}
        </div>
    );
};

// ============================================
// Textarea Component
// ============================================
export const Textarea = ({ 
    label, 
    error, 
    helperText,
    className = '',
    ...props 
}) => {
    return (
        <div className="space-y-1">
            {label && (
                <label className="block text-sm font-medium text-gray-700">
                    {label}
                    {props.required && <span className="text-red-500 ml-1">*</span>}
                </label>
            )}
            <textarea
                className={classNames(
                    'w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 transition-all',
                    {
                        'border-gray-300 focus:ring-primary-500 focus:border-primary-500': !error,
                        'border-red-500 focus:ring-red-500 focus:border-red-500': error,
                    },
                    className
                )}
                {...props}
            />
            {error && (
                <p className="text-sm text-red-600">{error}</p>
            )}
            {helperText && !error && (
                <p className="text-sm text-gray-500">{helperText}</p>
            )}
        </div>
    );
};

// ============================================
// Select Component
// ============================================
export const Select = ({ 
    label, 
    error, 
    options = [],
    placeholder,
    className = '',
    ...props 
}) => {
    return (
        <div className="space-y-1">
            {label && (
                <label className="block text-sm font-medium text-gray-700">
                    {label}
                    {props.required && <span className="text-red-500 ml-1">*</span>}
                </label>
            )}
            <select
                className={classNames(
                    'w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 transition-all',
                    {
                        'border-gray-300 focus:ring-primary-500 focus:border-primary-500': !error,
                        'border-red-500 focus:ring-red-500 focus:border-red-500': error,
                    },
                    className
                )}
                {...props}
            >
                {placeholder && <option value="">{placeholder}</option>}
                {options.map((option) => (
                    <option key={option.value} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </select>
            {error && (
                <p className="text-sm text-red-600">{error}</p>
            )}
        </div>
    );
};

// ============================================
// Checkbox Component
// ============================================
export const Checkbox = ({ label, error, className = '', ...props }) => {
    return (
        <div className="space-y-1">
            <label className="flex items-center space-x-2 cursor-pointer">
                <input
                    type="checkbox"
                    className={classNames(
                        'w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500',
                        className
                    )}
                    {...props}
                />
                <span className="text-sm text-gray-700">{label}</span>
            </label>
            {error && (
                <p className="text-sm text-red-600">{error}</p>
            )}
        </div>
    );
};

// ============================================
// Badge Component
// ============================================
export const Badge = ({ children, variant = 'default', size = 'md', className = '' }) => {
    const variants = {
        default: 'bg-gray-100 text-gray-800',
        primary: 'bg-primary-100 text-primary-800',
        success: 'bg-green-100 text-green-800',
        warning: 'bg-yellow-100 text-yellow-800',
        danger: 'bg-red-100 text-red-800',
        info: 'bg-blue-100 text-blue-800',
    };

    const sizes = {
        sm: 'px-2 py-0.5 text-xs',
        md: 'px-2.5 py-1 text-sm',
        lg: 'px-3 py-1.5 text-base',
    };

    return (
        <span className={classNames(
            'inline-flex items-center font-medium rounded-full',
            variants[variant],
            sizes[size],
            className
        )}>
            {children}
        </span>
    );
};

// ============================================
// Alert Component
// ============================================
export const Alert = ({ type = 'info', title, children, dismissible = false, onDismiss }) => {
    const [visible, setVisible] = useState(true);

    const types = {
        info: {
            bg: 'bg-blue-50',
            border: 'border-blue-200',
            icon: 'text-blue-400',
            text: 'text-blue-800',
        },
        success: {
            bg: 'bg-green-50',
            border: 'border-green-200',
            icon: 'text-green-400',
            text: 'text-green-800',
        },
        warning: {
            bg: 'bg-yellow-50',
            border: 'border-yellow-200',
            icon: 'text-yellow-400',
            text: 'text-yellow-800',
        },
        error: {
            bg: 'bg-red-50',
            border: 'border-red-200',
            icon: 'text-red-400',
            text: 'text-red-800',
        },
    };

    const handleDismiss = () => {
        setVisible(false);
        if (onDismiss) onDismiss();
    };

    if (!visible) return null;

    return (
        <div className={classNames(
            'rounded-lg border p-4',
            types[type].bg,
            types[type].border
        )}>
            <div className="flex">
                <div className="flex-shrink-0">
                    <svg className={classNames('h-5 w-5', types[type].icon)} viewBox="0 0 20 20" fill="currentColor">
                        <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                    </svg>
                </div>
                <div className="ml-3 flex-1">
                    {title && (
                        <h3 className={classNames('text-sm font-medium', types[type].text)}>
                            {title}
                        </h3>
                    )}
                    <div className={classNames('text-sm', types[type].text, title && 'mt-2')}>
                        {children}
                    </div>
                </div>
                {dismissible && (
                    <button
                        onClick={handleDismiss}
                        className={classNames('ml-3 inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2', types[type].icon)}
                    >
                        <svg className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                        </svg>
                    </button>
                )}
            </div>
        </div>
    );
};

// ============================================
// Modal Component
// ============================================
export const Modal = ({ isOpen, onClose, title, children, size = 'md' }) => {
    const modalRef = useRef(null);

    useEffect(() => {
        const handleEscape = (e) => {
            if (e.key === 'Escape') onClose();
        };

        if (isOpen) {
            document.addEventListener('keydown', handleEscape);
            document.body.style.overflow = 'hidden';
        }

        return () => {
            document.removeEventListener('keydown', handleEscape);
            document.body.style.overflow = 'unset';
        };
    }, [isOpen, onClose]);

    if (!isOpen) return null;

    const sizes = {
        sm: 'max-w-md',
        md: 'max-w-lg',
        lg: 'max-w-2xl',
        xl: 'max-w-4xl',
        full: 'max-w-full mx-4',
    };

    return (
        <div className="fixed inset-0 z-50 overflow-y-auto">
            <div className="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div 
                    className="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
                    onClick={onClose}
                />
                
                <div 
                    ref={modalRef}
                    className={classNames(
                        'relative inline-block w-full overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:align-middle',
                        sizes[size]
                    )}
                >
                    <div className="px-6 py-4 border-b border-gray-200">
                        <div className="flex items-center justify-between">
                            <h3 className="text-lg font-medium text-gray-900">
                                {title}
                            </h3>
                            <button
                                onClick={onClose}
                                className="text-gray-400 hover:text-gray-500 focus:outline-none"
                            >
                                <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div className="px-6 py-4">
                        {children}
                    </div>
                </div>
            </div>
        </div>
    );
};

// ============================================
// Loading States
// ============================================
export const Spinner = ({ size = 'md', className = '' }) => {
    const sizes = {
        xs: 'w-3 h-3 border-2',
        sm: 'w-4 h-4 border-2',
        md: 'w-8 h-8 border-4',
        lg: 'w-12 h-12 border-4',
        xl: 'w-16 h-16 border-4',
    };

    return (
        <div className={classNames(
            'border-primary-200 border-t-primary-600 rounded-full animate-spin',
            sizes[size],
            className
        )} />
    );
};

export const SkeletonLoader = ({ lines = 3, className = '' }) => {
    return (
        <div className={classNames('animate-pulse space-y-4', className)}>
            <div className="h-4 bg-gray-200 rounded w-3/4" />
            {Array.from({ length: lines }).map((_, i) => (
                <div key={i} className="h-4 bg-gray-200 rounded" />
            ))}
        </div>
    );
};

// ============================================
// Empty State
// ============================================
export const EmptyState = ({ 
    icon, 
    title, 
    description, 
    action,
    className = '' 
}) => {
    return (
        <div className={classNames('text-center py-12', className)}>
            <div className="text-6xl mb-4">{icon || '📭'}</div>
            <h3 className="text-2xl font-semibold text-gray-900 mb-2">
                {title}
            </h3>
            {description && (
                <p className="text-gray-600 mb-6 max-w-md mx-auto">
                    {description}
                </p>
            )}
            {action}
        </div>
    );
};

// ============================================
// Tooltip Component
// ============================================
export const Tooltip = ({ children, content, position = 'top' }) => {
    const [visible, setVisible] = useState(false);

    const positions = {
        top: 'bottom-full left-1/2 -translate-x-1/2 mb-2',
        bottom: 'top-full left-1/2 -translate-x-1/2 mt-2',
        left: 'right-full top-1/2 -translate-y-1/2 mr-2',
        right: 'left-full top-1/2 -translate-y-1/2 ml-2',
    };

    return (
        <div 
            className="relative inline-block"
            onMouseEnter={() => setVisible(true)}
            onMouseLeave={() => setVisible(false)}
        >
            {children}
            {visible && (
                <div className={classNames(
                    'absolute z-50 px-3 py-2 text-sm text-white bg-gray-900 rounded-lg shadow-lg whitespace-nowrap',
                    positions[position]
                )}>
                    {content}
                </div>
            )}
        </div>
    );
};

export default {
    Button,
    Card,
    CardHeader,
    CardBody,
    CardFooter,
    Input,
    Textarea,
    Select,
    Checkbox,
    Badge,
    Alert,
    Modal,
    Spinner,
    SkeletonLoader,
    EmptyState,
    Tooltip,
};
