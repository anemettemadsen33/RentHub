import { Page } from '@playwright/test';
import { waitForAppReady } from './app-ready';

export class NavigationHelper {
  constructor(private page: Page) {}

  async goToHome() {
    await this.page.goto('/');
    await waitForAppReady(this.page);
  }

  async goToProperties() {
    await this.page.goto('/properties');
    await waitForAppReady(this.page);
  }

  async goToDashboard() {
    await this.page.goto('/dashboard');
    await waitForAppReady(this.page);
  }

  async goToProfile() {
    await this.page.goto('/profile');
    await waitForAppReady(this.page);
  }

  async goToMessages() {
    await this.page.goto('/messages');
    await waitForAppReady(this.page);
  }

  async goToBookings() {
    await this.page.goto('/bookings');
    await waitForAppReady(this.page);
  }

  async goToSettings() {
    await this.page.goto('/settings');
    await waitForAppReady(this.page);
  }

  async goToFavorites() {
    await this.page.goto('/favorites');
    await waitForAppReady(this.page);
  }

  async clickNavLink(text: string) {
    await this.page.click(`nav a:has-text("${text}")`);
  }

  async waitForNavigation() {
    await waitForAppReady(this.page);
  }
}
