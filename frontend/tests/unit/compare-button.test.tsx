import { describe, it, expect, vi, beforeEach } from 'vitest';
import React from 'react';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { CompareButton } from '@/components/compare-button';
import apiClient from '@/lib/api-client';

vi.mock('@/lib/api-client');
vi.mock('@/hooks/use-toast', () => ({
  useToast: () => ({
    toast: vi.fn(),
  }),
}));

describe('CompareButton', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('renders compare button with label', () => {
    render(<CompareButton propertyId={1} showLabel={true} />);
    expect(screen.getByText('Compare')).toBeInTheDocument();
  });

  it('adds property to comparison when clicked', async () => {
    const mockPost = vi.spyOn(apiClient, 'post').mockResolvedValue({ data: {} });

    render(<CompareButton propertyId={1} showLabel={true} />);
    
    const button = screen.getByRole('button');
    fireEvent.click(button);

    await waitFor(() => {
      expect(mockPost).toHaveBeenCalledWith('/property-comparison/add', {
        property_id: 1,
      });
    });
  });

  it('removes property from comparison when clicked again', async () => {
    const mockPost = vi.spyOn(apiClient, 'post').mockResolvedValue({ data: {} });
    const mockDelete = vi.spyOn(apiClient, 'delete').mockResolvedValue({ data: {} });

    render(<CompareButton propertyId={1} showLabel={true} />);
    
    const button = screen.getByRole('button');
    
    // First click - add
    fireEvent.click(button);
    await waitFor(() => {
      expect(mockPost).toHaveBeenCalled();
    });

    // Second click - remove
    fireEvent.click(button);
    await waitFor(() => {
      expect(mockDelete).toHaveBeenCalledWith('/property-comparison/remove/1');
    });
  });

  it('shows loading state during API call', async () => {
    vi.spyOn(apiClient, 'post').mockImplementation(() => 
      new Promise(resolve => setTimeout(resolve, 100))
    );

    render(<CompareButton propertyId={1} showLabel={true} />);
    
    const button = screen.getByRole('button');
    fireEvent.click(button);

    expect(button).toBeDisabled();
  });
});
