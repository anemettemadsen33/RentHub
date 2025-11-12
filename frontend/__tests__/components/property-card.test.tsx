import { render, screen, fireEvent } from '@testing-library/react';
import { vi, describe, it, expect } from 'vitest';
import PropertyCard from '@/components/property-card';

const mockProperty = {
  id: 1,
  title: 'Beautiful Beach House',
  description: 'A stunning beachfront property',
  price_per_night: 150,
  bedrooms: 3,
  bathrooms: 2,
  guests: 6,
  city: 'Miami',
  country: 'USA',
  rating: 4.8,
  reviews_count: 42,
  images: [
    {
      id: 1,
      url: 'https://images.unsplash.com/photo-1',
      is_primary: true,
    },
  ],
  amenities: [
    { id: 1, name: 'WiFi', icon: 'wifi' },
    { id: 2, name: 'Pool', icon: 'pool' },
  ],
};

describe('PropertyCard', () => {
  it('should render property information correctly', () => {
    render(<PropertyCard property={mockProperty} />);

    expect(screen.getByText('Beautiful Beach House')).toBeInTheDocument();
    expect(screen.getByText('Miami, USA')).toBeInTheDocument();
    expect(screen.getByText('$150')).toBeInTheDocument();
    expect(screen.getByText(/night/i)).toBeInTheDocument();
  });

  it('should display property features', () => {
    render(<PropertyCard property={mockProperty} />);

    expect(screen.getByText(/3.*bedrooms/i)).toBeInTheDocument();
    expect(screen.getByText(/2.*bathrooms/i)).toBeInTheDocument();
    expect(screen.getByText(/6.*guests/i)).toBeInTheDocument();
  });

  it('should show rating and reviews count', () => {
    render(<PropertyCard property={mockProperty} />);

    expect(screen.getByText('4.8')).toBeInTheDocument();
    expect(screen.getByText(/42.*reviews/i)).toBeInTheDocument();
  });

  it('should call onClick when clicked', () => {
    const handleClick = vi.fn();
    render(<PropertyCard property={mockProperty} onClick={handleClick} />);

    const card = screen.getByRole('article');
    fireEvent.click(card);

    expect(handleClick).toHaveBeenCalledWith(mockProperty);
  });

  it('should handle missing image gracefully', () => {
    const propertyWithoutImage = { ...mockProperty, images: [] };
    render(<PropertyCard property={propertyWithoutImage} />);

    // Should render without crashing
    expect(screen.getByText('Beautiful Beach House')).toBeInTheDocument();
  });
});
