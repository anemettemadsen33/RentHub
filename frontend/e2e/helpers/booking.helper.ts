import { Page, expect } from '@playwright/test';

export class BookingHelper {
  constructor(private page: Page) {}

  async createBooking(params: {
    checkIn: string;
    checkOut: string;
    guests: number;
    specialRequests?: string;
  }) {
    await this.page.fill('input[name="check_in"], input[name="checkIn"]', params.checkIn);
    await this.page.fill('input[name="check_out"], input[name="checkOut"]', params.checkOut);
    await this.page.fill('input[name="guests"]', params.guests.toString());
    
    if (params.specialRequests) {
      await this.page.fill('textarea[name="special_requests"], textarea[name="notes"]', params.specialRequests);
    }
    
    await this.page.click('button:has-text("Book"), button:has-text("Confirm")');
    await this.page.waitForLoadState('networkidle');
  }

  async cancelBooking(bookingId: string) {
    await this.page.goto(`/bookings/${bookingId}`);
    await this.page.click('button:has-text("Cancel")');
    await this.page.click('button:has-text("Confirm cancellation")');
    await this.page.waitForLoadState('networkidle');
  }

  async viewBookingDetails(bookingId: string) {
    await this.page.goto(`/bookings/${bookingId}`);
    await this.page.waitForLoadState('networkidle');
  }

  async getBookingStatus(): Promise<string> {
    const status = await this.page.locator('[data-testid="booking-status"], .booking-status').first().textContent();
    return status || '';
  }

  async getTotalAmount(): Promise<string> {
    const amount = await this.page.locator('[data-testid="total-amount"], .total-amount').first().textContent();
    return amount || '';
  }
}
