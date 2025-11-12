import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { vi, describe, it, expect, beforeEach } from 'vitest';
import userEvent from '@testing-library/user-event';
import BookingForm from '@/components/booking-form';

const mockProperty = {
  id: 1,
  price_per_night: 100,
  guests: 4,
  title: 'Test Property',
};

describe('BookingForm', () => {
  const mockOnSubmit = vi.fn();

  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('should render form fields correctly', () => {
    render(<BookingForm property={mockProperty} onSubmit={mockOnSubmit} />);

    expect(screen.getByLabelText(/check-in/i)).toBeInTheDocument();
    expect(screen.getByLabelText(/check-out/i)).toBeInTheDocument();
    expect(screen.getByLabelText(/guests/i)).toBeInTheDocument();
    expect(screen.getByRole('button', { name: /book now/i })).toBeInTheDocument();
  });

  it('should calculate total price based on dates', async () => {
    render(<BookingForm property={mockProperty} onSubmit={mockOnSubmit} />);

    const checkIn = screen.getByLabelText(/check-in/i);
    const checkOut = screen.getByLabelText(/check-out/i);

    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const nextWeek = new Date();
    nextWeek.setDate(nextWeek.getDate() + 4); // 3 nights

    await userEvent.type(checkIn, tomorrow.toISOString().split('T')[0]);
    await userEvent.type(checkOut, nextWeek.toISOString().split('T')[0]);

    await waitFor(() => {
      expect(screen.getByText(/\$300/)).toBeInTheDocument(); // 3 nights * $100
    });
  });

  it('should validate guest count', async () => {
    render(<BookingForm property={mockProperty} onSubmit={mockOnSubmit} />);

    const guestsInput = screen.getByLabelText(/guests/i);
    
    await userEvent.clear(guestsInput);
    await userEvent.type(guestsInput, '10'); // More than max guests

    const submitButton = screen.getByRole('button', { name: /book now/i });
    fireEvent.click(submitButton);

    await waitFor(() => {
      expect(screen.getByText(/maximum.*4.*guests/i)).toBeInTheDocument();
    });
  });

  it('should submit form with correct data', async () => {
    render(<BookingForm property={mockProperty} onSubmit={mockOnSubmit} />);

    const checkIn = screen.getByLabelText(/check-in/i);
    const checkOut = screen.getByLabelText(/check-out/i);
    const guestsInput = screen.getByLabelText(/guests/i);

    await userEvent.type(checkIn, '2025-02-01');
    await userEvent.type(checkOut, '2025-02-05');
    await userEvent.clear(guestsInput);
    await userEvent.type(guestsInput, '2');

    const submitButton = screen.getByRole('button', { name: /book now/i });
    fireEvent.click(submitButton);

    await waitFor(() => {
      expect(mockOnSubmit).toHaveBeenCalledWith({
        property_id: 1,
        check_in: '2025-02-01',
        check_out: '2025-02-05',
        guests: 2,
      });
    });
  });
});
