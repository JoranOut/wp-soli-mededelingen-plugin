const { test, expect } = require('@playwright/test');
const { seedFixtures, loginAsAdmin, expectNoPhpDiagnostics } = require('./helpers');

let fixtures;

test.beforeAll(() => {
	fixtures = seedFixtures();
});

test.describe('renders without PHP errors', () => {
	// Logged in throughout: a logged-out visitor gets a 403 wp_die() page on the
	// single mededeling and the archive, and an empty query loop. Asserting "no
	// PHP diagnostics" against those pages says nothing about the code paths that
	// actually render a mededeling, so every surface here is visited as a member.
	test.beforeEach(async ({ page }) => {
		await loginAsAdmin(page);
	});

	test('on the single mededeling page', async ({ page }) => {
		await page.goto(fixtures.mededelingUrl);
		await expect(page.locator('body')).toContainText('Geheime mededeling voor leden');
		await expectNoPhpDiagnostics(page);
	});

	test('on the mededelingen archive', async ({ page }) => {
		await page.goto('/?post_type=soli_mededeling');
		await expectNoPhpDiagnostics(page);
	});

	test('on a page with the mededelingen query loop', async ({ page }) => {
		await page.goto(fixtures.pageUrl);
		await expect(page.locator('body')).toContainText('Geheime mededeling voor leden');
		await expectNoPhpDiagnostics(page);
	});

	test('on a site search, where the post type is excluded per request', async ({ page }) => {
		await page.goto('/?s=Geheime+mededeling');
		await expectNoPhpDiagnostics(page);
	});

	test('in the wp-admin list table', async ({ page }) => {
		await page.goto('/wp-admin/edit.php?post_type=soli_mededeling');
		await expectNoPhpDiagnostics(page);
	});

	test('in the block editor for a mededeling', async ({ page }) => {
		await page.goto(`/wp-admin/post.php?post=${fixtures.mededelingId}&action=edit`);
		await expectNoPhpDiagnostics(page);
	});
});
