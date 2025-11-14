import { Page, Locator } from '@playwright/test';

export class FormHelper {
  constructor(private page: Page) {}

  async fillInput(selector: string, value: string) {
    await this.page.fill(selector, value);
  }

  async selectOption(selector: string, value: string) {
    await this.page.selectOption(selector, value);
  }

  async clickButton(text: string) {
    await this.page.click(`button:has-text("${text}")`);
  }

  async uploadFile(selector: string, filePath: string) {
    await this.page.setInputFiles(selector, filePath);
  }

  async checkCheckbox(selector: string) {
    await this.page.check(selector);
  }

  async uncheckCheckbox(selector: string) {
    await this.page.uncheck(selector);
  }

  async submitForm(formSelector: string = 'form') {
    await this.page.click(`${formSelector} button[type="submit"]`);
  }

  async waitForFormSubmission() {
    await this.page.waitForResponse(response => 
      response.status() === 200 || response.status() === 201
    );
  }

  async getFieldError(fieldName: string): Promise<string | null> {
    const errorElement = this.page.locator(`[data-error="${fieldName}"]`);
    if (await errorElement.isVisible()) {
      return await errorElement.textContent();
    }
    return null;
  }
}
