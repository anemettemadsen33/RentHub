import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import { PropertyCard } from '@/components/property-card';
import { Property } from '@/types';

const mockProperty: Property = {
  id: 1,
  title: 'Test Property',
  description: 'Test description',
  price: 100,
  price_per_night: 100,
  currency: 'USD',
  type: 'apartment',
  address: '123 Test St',
  city: 'Test City',
  country: 'Test Country',
  bedrooms: 2,
  bathrooms: 1,
  max_guests: 4,
  image_url: '/test-image.jpg',
  images: ['/test-image.jpg'],
  rating: 4.5,
  review_count: 10,
  amenities: ['WiFi', 'Kitchen'],
  status: 'available',
  created_at: new Date().toISOString(),
  updated_at: new Date().toISOString(),
};

describe('PropertyCard', () => {
  it('renders property title and address', () => {
    render(<PropertyCard property={mockProperty} />);
    
    expect(screen.getByText('Test Property')).toBeInTheDocument();
    expect(screen.getByText(/Test City/)).toBeInTheDocument();
  });

  it('displays price correctly', () => {
    render(<PropertyCard property={mockProperty} />);
    
    expect(screen.getByText(/\$100/)).toBeInTheDocument();
  });

  it('shows amenity counts', () => {
    render(<PropertyCard property={mockProperty} />);
    
    expect(screen.getByText(/2/)).toBeInTheDocument(); // bedrooms
    expect(screen.getByText(/1/)).toBeInTheDocument(); // bathrooms
    expect(screen.getByText(/4/)).toBeInTheDocument(); // guests
  });

  it('has accessible alt text for image', () => {
    render(<PropertyCard property={mockProperty} />);
    
    const image = screen.getByRole('img', { name: /Test Property/ });
    expect(image).toBeInTheDocument();
  });
});
