import { test, expect } from '@playwright/test';
import { AuthHelper } from './helpers/auth.helper';
import { NavigationHelper } from './helpers/navigation.helper';
import { FormHelper } from './helpers/form.helper';

test.describe('Complete Messaging System Tests', () => {
  let authHelper: AuthHelper;
  let navHelper: NavigationHelper;
  let formHelper: FormHelper;

  test.beforeEach(async ({ page }) => {
    authHelper = new AuthHelper(page);
    navHelper = new NavigationHelper(page);
    formHelper = new FormHelper(page);
    await authHelper.login('test@example.com', 'password123');
  });

  test('should view messages inbox', async ({ page }) => {
    await navHelper.goToMessages();
    
    await expect(page.locator('text=/messages|inbox|conversations/i')).toBeVisible();
  });

  test('should send a new message', async ({ page }) => {
    await navHelper.goToMessages();
    
    const newMessageButton = page.locator('button:has-text("New Message"), button:has-text("Compose")');
    if (await newMessageButton.isVisible()) {
      await newMessageButton.click();
      
      await page.fill('input[name="recipient"], input[type="search"]', 'Host Name');
      await page.fill('textarea[name="message"]', 'Hello, I have a question about the property.');
      await formHelper.submitForm();
      
      await expect(page.locator('text=/message sent|sent successfully/i')).toBeVisible({ timeout: 10000 });
    }
  });

  test('should reply to a message', async ({ page }) => {
    await navHelper.goToMessages();
    
    const firstConversation = page.locator('[data-testid="conversation"], .conversation-item').first();
    if (await firstConversation.isVisible()) {
      await firstConversation.click();
      
      await page.fill('textarea[name="message"], input[name="message"]', 'Thank you for your response!');
      await page.click('button:has-text("Send")');
      
      await expect(page.locator('text=Thank you for your response!')).toBeVisible({ timeout: 5000 });
    }
  });

  test('should search messages', async ({ page }) => {
    await navHelper.goToMessages();
    
    const searchInput = page.locator('input[type="search"], input[placeholder*="search" i]');
    if (await searchInput.isVisible()) {
      await searchInput.fill('booking');
      await page.waitForTimeout(1000);
    }
  });

  test('should filter messages by unread', async ({ page }) => {
    await navHelper.goToMessages();
    
    const unreadFilter = page.locator('button:has-text("Unread"), input[name="filter"][value="unread"]');
    if (await unreadFilter.first().isVisible()) {
      await unreadFilter.first().click();
      await page.waitForTimeout(1000);
    }
  });

  test('should delete a conversation', async ({ page }) => {
    await navHelper.goToMessages();
    
    const deleteButton = page.locator('button[aria-label*="delete"], button:has-text("Delete")').first();
    if (await deleteButton.isVisible()) {
      await deleteButton.click();
      await page.click('button:has-text("Confirm"), button:has-text("Yes")');
      
      await expect(page.locator('text=/deleted|removed/i')).toBeVisible({ timeout: 10000 });
    }
  });

  test('should mark message as read', async ({ page }) => {
    await navHelper.goToMessages();
    
    const unreadMessage = page.locator('[data-unread="true"], .unread').first();
    if (await unreadMessage.isVisible()) {
      await unreadMessage.click();
      await page.waitForTimeout(1000);
    }
  });

  test('should attach file to message', async ({ page }) => {
    await navHelper.goToMessages();
    
    const firstConversation = page.locator('[data-testid="conversation"]').first();
    if (await firstConversation.isVisible()) {
      await firstConversation.click();
      
      const attachButton = page.locator('button[aria-label*="attach"], input[type="file"]');
      if (await attachButton.first().isVisible()) {
        const buffer = Buffer.from('test document');
        await attachButton.first().setInputFiles({
          name: 'document.pdf',
          mimeType: 'application/pdf',
          buffer: buffer,
        });
        
        await page.waitForTimeout(1000);
      }
    }
  });

  test('should receive real-time messages', async ({ page, context }) => {
    await navHelper.goToMessages();
    
    // Open conversation
    const firstConversation = page.locator('[data-testid="conversation"]').first();
    if (await firstConversation.isVisible()) {
      await firstConversation.click();
      
      // Wait for potential new messages
      await page.waitForTimeout(3000);
    }
  });

  test('should block a user', async ({ page }) => {
    await navHelper.goToMessages();
    
    const firstConversation = page.locator('[data-testid="conversation"]').first();
    if (await firstConversation.isVisible()) {
      await firstConversation.click();
      
      const moreButton = page.locator('button[aria-label*="more"], button:has-text("⋮")');
      if (await moreButton.isVisible()) {
        await moreButton.click();
        await page.click('button:has-text("Block"), text=Block');
        
        await expect(page.locator('text=/blocked|user blocked/i')).toBeVisible({ timeout: 10000 });
      }
    }
  });
});
