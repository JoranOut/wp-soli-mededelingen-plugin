const { test, expect } = require('@playwright/test');
const { seedFixtures, loginAsAdmin } = require('./helpers');

test.beforeAll(() => {
	seedFixtures();
});

test.describe('Mededelingen post type', () => {
	test.beforeEach(async ({ page }) => {
		await loginAsAdmin(page);
	});

	test('shows the Mededelingen menu in wp-admin', async ({ page }) => {
		await page.goto('/wp-admin/');
		await expect(page.locator('#menu-posts-soli_mededeling')).toBeVisible();
	});

	test('lists the seeded mededeling in the admin list table', async ({ page }) => {
		await page.goto('/wp-admin/edit.php?post_type=soli_mededeling');
		await expect(page.locator('.wp-heading-inline')).toHaveText('Mededelingen');
		await expect(
			page.getByRole('link', { name: 'Geheime mededeling voor leden' }).first()
		).toBeVisible();
	});

	test('registers the Mededelingen Query Loop variation in the editor', async ({ page }) => {
		await page.goto('/wp-admin/post-new.php?post_type=page');

		await page.waitForFunction(
			() =>
				window.wp?.blocks
					?.getBlockVariations('core/query')
					?.some((v) => v.name === 'soli-mededelingen/mededelingen-loop'),
			undefined,
			{ timeout: 30000 }
		);
	});
});
