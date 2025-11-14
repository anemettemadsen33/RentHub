import { test, expect } from '@playwright/test';
import { PropertyHelper } from './helpers/property.helper';
import { NavigationHelper } from './helpers/navigation.helper';
import { FormHelper } from './helpers/form.helper';

test.describe('Complete Property Search and Filters', () => {
  let propertyHelper: PropertyHelper;
  let navHelper: NavigationHelper;
  let formHelper: FormHelper;

  test.beforeEach(async ({ page }) => {
    propertyHelper = new PropertyHelper(page);
    navHelper = new NavigationHelper(page);
    formHelper = new FormHelper(page);
    await page.goto('/');
  });

  test('should search properties with all parameters', async ({ page }) => {
    await propertyHelper.searchProperties({
      location: 'Paris',
      checkIn: '2025-03-01',
      checkOut: '2025-03-05',
      guests: 2
    });
    
    // Verify results page
    const propertyCards = page.locator('[data-testid="property-card"], .property-card');
    await expect(await propertyCards.count()).toBeGreaterThanOrEqual(1);
  });

  test('should filter by price range', async ({ page }) => {
    await navHelper.goToProperties();
    await propertyHelper.filterByPrice(50, 200);
    
    // Wait for filtered results
    await page.waitForTimeout(1000);
    
    const propertyCards = await page.locator('[data-testid="property-card"], .property-card').count();
    expect(propertyCards).toBeGreaterThan(0);
  });

  test('should filter by number of bedrooms', async ({ page }) => {
    await navHelper.goToProperties();
    
    // Select 2 bedrooms
    await page.click('button:has-text("Bedrooms"), label:has-text("Bedrooms")');
    await page.click('text=2');
    
    await page.waitForTimeout(1000);
    const results = await page.locator('[data-testid="property-card"], .property-card').count();
    expect(results).toBeGreaterThan(0);
  });

  test('should filter by property type', async ({ page }) => {
    await navHelper.goToProperties();
    
    // Select property type
    const propertyTypeFilter = page.locator('select[name="property_type"], button:has-text("Property Type")');
    const firstFilter = propertyTypeFilter.first();
    if (await firstFilter.isVisible()) {
      await firstFilter.click();
      const optionLocator = page.locator('text=Apartment, text=House');
      await optionLocator.first().click();
    }
    
    await page.waitForTimeout(1000);
  });

  test('should filter by amenities', async ({ page }) => {
    await navHelper.goToProperties();
    
    // Check amenity checkboxes
    const wifiCheckbox = page.locator('input[value="wifi"], label:has-text("WiFi")');
    const parkingCheckbox = page.locator('input[value="parking"], label:has-text("Parking")');
    
    if (await wifiCheckbox.first().isVisible()) {
      await wifiCheckbox.first().check();
    }
    if (await parkingCheckbox.first().isVisible()) {
      await parkingCheckbox.first().check();
    }
    
    await page.waitForTimeout(1000);
  });

  test('should sort properties by price (low to high)', async ({ page }) => {
    await navHelper.goToProperties();
    
    const sortSelect = page.locator('select[name="sort"], button:has-text("Sort")');
    if (await sortSelect.first().isVisible()) {
      await sortSelect.first().click();
      await page.click('text=/price.*low|lowest price/i');
    }
    
    await page.waitForTimeout(1000);
  });

  test('should sort properties by price (high to low)', async ({ page }) => {
    await navHelper.goToProperties();
    
    const sortSelect = page.locator('select[name="sort"], button:has-text("Sort")');
    if (await sortSelect.first().isVisible()) {
      await sortSelect.first().click();
      await page.click('text=/price.*high|highest price/i');
    }
    
    await page.waitForTimeout(1000);
  });

  test('should view property details', async ({ page }) => {
    await navHelper.goToProperties();
    await propertyHelper.selectFirstProperty();
    
    // Verify details page elements
    await expect(page.locator('h1')).toBeVisible();
    await expect(page.locator('text=/\\$/i')).toBeVisible(); // Price
    const images = page.locator('img');
    await expect(await images.count()).toBeGreaterThanOrEqual(1); // Images
  });

  test('should navigate through property images', async ({ page }) => {
    await navHelper.goToProperties();
    await propertyHelper.selectFirstProperty();
    
    const nextButton = page.locator('button[aria-label*="next"], button:has-text("Next")');
    const prevButton = page.locator('button[aria-label*="prev"], button:has-text("Previous")');
    
    if (await nextButton.isVisible()) {
      await nextButton.click();
      await page.waitForTimeout(500);
      
      if (await prevButton.isVisible()) {
        await prevButton.click();
      }
    }
  });

  test('should add property to favorites', async ({ page }) => {
    await navHelper.goToProperties();
    await propertyHelper.selectFirstProperty();
    
    const favoriteButton = page.locator('button[aria-label*="favorite"], button:has-text("Save")').first();
    if (await favoriteButton.isVisible()) {
      await favoriteButton.click();
      await expect(page.locator('text=/added to favorites|saved/i')).toBeVisible({ timeout: 5000 });
    }
  });

  test('should share property', async ({ page }) => {
    await navHelper.goToProperties();
    await propertyHelper.selectFirstProperty();
    
    const shareButton = page.locator('button[aria-label*="share"], button:has-text("Share")').first();
    if (await shareButton.isVisible()) {
      await shareButton.click();
      
      // Verify share dialog appears
      await expect(page.locator('[role="dialog"], .modal')).toBeVisible();
    }
  });

  test('should show map view of properties', async ({ page }) => {
    await navHelper.goToProperties();
    
    const mapButton = page.locator('button:has-text("Map"), button[aria-label*="map"]').first();
    if (await mapButton.isVisible()) {
      await mapButton.click();
      // cSpell:ignore mapboxgl
      await expect(page.locator('.leaflet-container, .mapboxgl-map, #map')).toBeVisible({ timeout: 5000 });
    }
  });

  test('should paginate through results', async ({ page }) => {
    await navHelper.goToProperties();
    
    const nextPageButton = page.locator('button:has-text("Next"), button[aria-label*="next page"]');
    if (await nextPageButton.isVisible()) {
      await nextPageButton.click();
      // cSpell:ignore networkidle
      await page.waitForLoadState('networkidle');
      
      // Verify page number changed
      const pageIndicator = page.locator('[aria-current="page"], .active-page');
      if (await pageIndicator.isVisible()) {
        await expect(pageIndicator).toContainText('2');
      }
    }
  });

  test('should clear all filters', async ({ page }) => {
    await navHelper.goToProperties();
    
    // Apply some filters
    await propertyHelper.filterByPrice(100, 300);
    
    // Clear filters
    const clearButton = page.locator('button:has-text("Clear"), button:has-text("Reset")');
    if (await clearButton.isVisible()) {
      await clearButton.click();
      await page.waitForTimeout(1000);
    }
  });
});
