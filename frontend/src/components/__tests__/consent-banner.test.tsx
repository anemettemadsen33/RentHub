import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import { ConsentBanner } from '@/components/consent-banner';

// Mock localStorage
const localStorageMock = (() => {
  let store: Record<string, string> = {};

  return {
    getItem: (key: string) => store[key] || null,
    setItem: (key: string, value: string) => {
      store[key] = value.toString();
    },
    removeItem: (key: string) => {
      delete store[key];
    },
    clear: () => {
      store = {};
    },
  };
})();

Object.defineProperty(window, 'localStorage', {
  value: localStorageMock,
});

describe('ConsentBanner', () => {
  beforeEach(() => {
    localStorageMock.clear();
  });

  it('renders banner when consent not given', () => {
    render(<ConsentBanner />);

    expect(screen.getByText(/cookies/i)).toBeInTheDocument();
    expect(screen.getByRole('button', { name: /accept/i })).toBeInTheDocument();
  });

  it('does not render when consent already given', () => {
    localStorageMock.setItem('cookieConsent', 'true');
    
    render(<ConsentBanner />);

    expect(screen.queryByText(/cookies/i)).not.toBeInTheDocument();
  });

  it('hides banner and saves consent when accept is clicked', () => {
    render(<ConsentBanner />);

    const acceptButton = screen.getByRole('button', { name: /accept/i });
    fireEvent.click(acceptButton);

    expect(localStorageMock.getItem('cookieConsent')).toBe('true');
    expect(screen.queryByText(/cookies/i)).not.toBeInTheDocument();
  });

  it('shows decline button if provided', () => {
    render(<ConsentBanner showDecline />);

    expect(screen.getByRole('button', { name: /decline/i })).toBeInTheDocument();
  });

  it('hides banner when decline is clicked', () => {
    render(<ConsentBanner showDecline />);

    const declineButton = screen.getByRole('button', { name: /decline/i });
    fireEvent.click(declineButton);

    expect(screen.queryByText(/cookies/i)).not.toBeInTheDocument();
  });

  it('renders link to privacy policy', () => {
    render(<ConsentBanner />);

    const link = screen.getByRole('link', { name: /privacy/i });
    expect(link).toBeInTheDocument();
    expect(link).toHaveAttribute('href', '/privacy');
  });
});
