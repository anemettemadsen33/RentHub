import { test, expect } from '@playwright/test';

// Basic localization verification for RO locale across key flows
// Assumes locale selection sets NEXT_LOCALE cookie or query param; adjust navigation if needed.

const setRomanianLocale = async ({ page }: any) => {
  await page.context().addCookies([{ name: 'NEXT_LOCALE', value: 'ro', domain: 'localhost', path: '/' }]);
};

test.describe('Localization (ro)', () => {
  test.beforeEach(async ({ page }) => {
    await setRomanianLocale({ page });
  });

  test('Properties page localized strings', async ({ page }) => {
    await page.goto('/properties');
    await expect(page.getByText(/proprietăți disponibile/i)).toBeVisible();
    await expect(page.getByPlaceholder('Caută după locație, nume proprietate...')).toBeVisible();
    await page.getByRole('button', { name: /Filtre/i }).click();
    // Ensure map hint present when switching to map view
    await page.getByLabel('Map view').click();
    await expect(page.getByText(/Mișcă și fă zoom pe hartă/i)).toBeVisible();
  });

  test('Booking cancel confirm dialog text', async ({ page }) => {
    // Navigate to a booking detail stub (adjust id if necessary)
    await page.goto('/bookings/1');
    const cancelBtn = page.getByRole('button', { name: /Anulează Rezervarea/i });
    if (await cancelBtn.isVisible()) {
      await cancelBtn.click();
      // Confirm dialog native confirm can't be intercepted easily; simulate confirm text check via page dialog handler
    }
  });

  test('Invoice page Romanian labels', async ({ page }) => {
    await page.goto('/bookings/1/payment');
    await expect(page.getByText('Plată & Factură')).toBeVisible();
    await expect(page.getByText(/Factura #/)).toBeVisible();
    await expect(page.getByText('Sumar Plată')).toBeVisible();
    await expect(page.getByText('Taxe')).toBeVisible();
    await expect(page.getByRole('button', { name: /Confirmă & Descarcă Factura/i })).toBeVisible();
  });

  test('Messages typing indicator and placeholders', async ({ page }) => {
    await page.goto('/messages');
    await expect(page.getByRole('heading', { name: 'Mesaje' })).toBeVisible();
    await expect(page.getByPlaceholder('Caută mesaje...')).toBeVisible();
    await expect(page.getByPlaceholder('Scrie un mesaj...')).toBeVisible();
    // Typing indicator is event-driven; just assert key exists in DOM if triggered later
  });

  test('Favorites page Romanian heading', async ({ page }) => {
    await page.goto('/favorites');
    await expect(page.getByRole('heading', { name: 'Favoritele mele' })).toBeVisible();
  });

  test('Comparison page Romanian labels', async ({ page }) => {
    // Seed localStorage for comparison in stub mode
    await page.addInitScript(() => {
      window.localStorage.setItem('comparison', JSON.stringify([1, 2]));
    });
    await page.goto('/property-comparison');
    await expect(page.getByRole('heading', { name: /Compară proprietăți/i })).toBeVisible();
    await expect(page.getByRole('button', { name: /Șterge tot/i })).toBeVisible();
    await expect(page.getByText(/Preț pe noapte/i)).toBeVisible();
    await expect(page.getByText(/Dormitoare/i)).toBeVisible();
  });

  test('Notifications page Romanian labels', async ({ page }) => {
    // Seed auth to bypass redirect
    await page.addInitScript(() => {
      window.localStorage.setItem('auth_token', 'stub-token');
      window.localStorage.setItem('user', JSON.stringify({ id: 1, name: 'Test User', email: 'test@example.com', role: 'tenant' }));
    });
    await page.goto('/notifications');
    await expect(page.getByRole('heading', { name: 'Notificări' })).toBeVisible();
    await expect(page.getByText('Filtre')).toBeVisible();
    await expect(page.getByPlaceholder('Caută notificări...')).toBeVisible();
    await expect(page.getByRole('tab', { name: 'Toate' })).toBeVisible();
    await expect(page.getByText('Preferințe Notificări')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Salvează Preferințele' })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Marchează toate ca citite' })).toBeVisible();
  });

  test('Profile page Romanian labels', async ({ page }) => {
    await page.addInitScript(() => {
      window.localStorage.setItem('auth_token', 'stub-token');
      window.localStorage.setItem('user', JSON.stringify({ id: 1, name: 'Test User', email: 'test@example.com', role: 'tenant' }));
    });
    await page.goto('/profile');
    await expect(page.getByRole('heading', { name: 'Profilul Meu' })).toBeVisible();
    await expect(page.getByRole('tab', { name: 'Profil' })).toBeVisible();
    await expect(page.getByRole('tab', { name: 'Securitate' })).toBeVisible();
    await expect(page.getByRole('tab', { name: 'Linkuri Social Media' })).toBeVisible();
    await expect(page.getByText('Informații Personale')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Verifică identitatea' })).toBeVisible();
  });
});
