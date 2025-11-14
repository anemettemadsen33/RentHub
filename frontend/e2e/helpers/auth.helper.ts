import { Page, expect } from '@playwright/test';

export class AuthHelper {
  constructor(private page: Page) {}

  async register(email: string, password: string, name: string = 'Test User') {
    await this.page.goto('/auth/register');
    await this.page.fill('input[name="name"]', name);
    await this.page.fill('input[name="email"]', email);
    await this.page.fill('input[name="password"]', password);
    await this.page.fill('input[name="password_confirmation"]', password);
    await this.page.click('button[type="submit"]');
  }

  async login(email: string, password: string) {
    await this.page.goto('/auth/login');
    await this.page.fill('input[name="email"]', email);
    await this.page.fill('input[name="password"]', password);
    await this.page.click('button[type="submit"]');
    await this.page.waitForLoadState('networkidle');
  }

  async logout() {
    await this.page.click('[aria-label="User menu"]');
    await this.page.click('text=Logout');
    await this.page.waitForLoadState('networkidle');
  }

  async isLoggedIn(): Promise<boolean> {
    const token = await this.page.evaluate(() => localStorage.getItem('token'));
    return token !== null;
  }

  async getUserEmail(): Promise<string | null> {
    return await this.page.evaluate(() => {
      const user = localStorage.getItem('user');
      return user ? JSON.parse(user).email : null;
    });
  }
}
