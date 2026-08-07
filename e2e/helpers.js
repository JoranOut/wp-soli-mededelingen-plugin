const { execSync } = require('child_process');

const PLUGIN_PATH = '/var/www/html/wp-content/plugins/wp-soli-mededelingen-plugin';

/**
 * Run a wp-cli command inside the tests instance (the one e2e runs against).
 */
function wpCli(command) {
	return execSync(`npx wp-env run tests-cli -- wp ${command}`, {
		encoding: 'utf8',
	}).trim();
}

/**
 * Seed the fixture content (a published mededeling and a page with a
 * mededelingen query loop) and return { mededelingId, mededelingUrl, pageUrl }.
 */
function seedFixtures() {
	// --user=admin: the plugin hides mededelingen from logged-out queries,
	// which would break the seed's own idempotency lookup.
	const output = wpCli(`eval-file ${PLUGIN_PATH}/e2e/fixtures/seed.php --user=admin`);
	// wp-env may prefix docker output; the JSON is on the last line.
	const json = output.slice(output.indexOf('{'));
	return JSON.parse(json);
}

async function loginAsAdmin(page) {
	await page.goto('/wp-login.php');
	await page.fill('#user_login', 'admin');
	await page.fill('#user_pass', 'password');
	await page.click('#wp-submit');
	await page.waitForURL(/wp-admin/);
}

/**
 * Fetch a REST nonce for the logged-in browser context, so REST requests
 * authenticate the way the block editor does.
 */
async function getRestNonce(page) {
	const response = await page.request.get('/wp-admin/admin-ajax.php?action=rest-nonce');
	return (await response.text()).trim();
}

module.exports = { wpCli, seedFixtures, loginAsAdmin, getRestNonce };
