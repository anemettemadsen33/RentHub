import { Page, expect } from '@playwright/test';

// cSpell:ignore networkidle

export class PropertyHelper {
  constructor(private page: Page) {}

  async searchProperties(params: {
    location?: string;
    checkIn?: string;
    checkOut?: string;
    guests?: number;
  }) {
    if (params.location) {
      await this.page.fill('input[name="location"], input[placeholder*="location" i]', params.location);
    }
    if (params.checkIn) {
      await this.page.fill('input[name="check_in"], input[name="checkIn"]', params.checkIn);
    }
    if (params.checkOut) {
      await this.page.fill('input[name="check_out"], input[name="checkOut"]', params.checkOut);
    }
    if (params.guests) {
      await this.page.fill('input[name="guests"]', params.guests.toString());
    }
    
    await this.page.click('button:has-text("Search")');
    await this.page.waitForLoadState('networkidle');
  }

  async filterByPrice(minPrice: number, maxPrice: number) {
    await this.page.fill('input[name="min_price"], input[name="minPrice"]', minPrice.toString());
    await this.page.fill('input[name="max_price"], input[name="maxPrice"]', maxPrice.toString());
    await this.page.click('button:has-text("Apply"), button:has-text("Filter")');
    await this.page.waitForLoadState('networkidle');
  }

  async filterByBedrooms(bedrooms: number) {
    await this.page.fill('input[name="bedrooms"]', bedrooms.toString());
    await this.page.click('button:has-text("Apply"), button:has-text("Filter")');
    await this.page.waitForLoadState('networkidle');
  }

  async selectFirstProperty() {
    const propertyCard = this.page.locator('[data-testid="property-card"], .property-card').first();
    await propertyCard.waitFor({ state: 'visible' });
    await propertyCard.click();
    await this.page.waitForLoadState('networkidle');
  }

  async addToFavorites() {
    await this.page.click('button[aria-label="Add to favorites"], button:has-text("Save")');
  }

  async getPropertyTitle(): Promise<string> {
    return await this.page.locator('h1').first().textContent() || '';
  }

  async getPropertyPrice(): Promise<string> {
    return await this.page.locator('[data-testid="property-price"], .property-price').first().textContent() || '';
  }
}
