const { execSync } = require('child_process');
const { expect } = require('@playwright/test');

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

/**
 * WP_DEBUG and WP_DEBUG_DISPLAY are enabled for the tests environment in
 * .wp-env.json (guarded by debug-mode.spec.js), so PHP diagnostics are printed
 * into the rendered document. Anything emitted before <html> or inside <head>
 * is relocated into the body by the HTML parser, so reading the body text
 * catches diagnostics from any point in the request.
 *
 * Fatals and parse errors are never acceptable, wherever they come from.
 * Warnings, notices and deprecations are scoped to this plugin's own PHP files,
 * so unrelated WordPress core or theme noise cannot turn CI red.
 */
const FATAL_ERROR_PATTERN = /Fatal error|Parse error/i;

/** This plugin's own PHP files: the main file, the two classes, the updater. */
const PLUGIN_PHP_FILES =
	'wp-soli-mededelingen-plugin\\.php|class-soli-mededelingen-(?:post-type|visibility)\\.php|updater\\.php';

const PLUGIN_DIAGNOSTIC_PATTERN = new RegExp(
	'(Warning|Notice|Deprecated):[^\\n]*(' + PLUGIN_PHP_FILES + ')',
	'i'
);

/**
 * Assert the currently loaded page contains no PHP diagnostics.
 *
 * @param {import('@playwright/test').Page} page
 */
async function expectNoPhpDiagnostics(page) {
	const url = page.url();
	// Two reads of the same body, because the two assertions want different
	// scopes:
	//
	// - PLUGIN_DIAGNOSTIC_PATTERN matches within a line ([^\n]*), and wp-admin
	//   prints large single-line JSON blobs into inline <script>. A string in
	//   such a blob containing `Warning:` near a plugin path would match and
	//   turn CI red for nothing. That pattern must NOT see script text, so it
	//   reads `markup`: a body clone with script/style/template/noscript
	//   removed. Scoping the read is the fix; loosening the pattern to tolerate
	//   script noise would blunt the diagnostic itself.
	// - FATAL_ERROR_PATTERN must see script text. A fatal thrown while an
	//   inline script is being printed lands inside that <script> node, and a
	//   stripped clone would lose it. So it reads `full`. `Fatal error` /
	//   `Parse error` are far less likely to appear in script text than
	//   `Warning:`.
	//
	// textContent, NOT innerText. innerText returns *rendered* text and
	// skips anything hidden (display:none, [hidden], a collapsed panel), so a
	// diagnostic emitted inside a hidden container never reaches the assertion
	// and this helper passes vacuously. Measured with one injected error in two
	// sibling repos: wp-soli-ticket-scanner-plugin (3 specs failed with
	// textContent, only 2 with innerText) and wp-soli-taken-plugin, where the
	// injection sat inside display:none (3 failed with textContent, all 5 still
	// passed with innerText — completely blind). Measured here too: the same
	// injection wrapped in <div style="display:none"> fails 4 specs with
	// textContent and 0 with innerText. wp-admin in particular ships large
	// amounts of markup hidden by default. Do not change this back.
	const { full, markup } = await page.evaluate(() => {
		const clone = document.body.cloneNode(true);
		clone
			.querySelectorAll('script, style, template, noscript')
			.forEach((node) => node.remove());

		return {
			full: document.body.textContent || '',
			markup: clone.textContent || '',
		};
	});

	expect(full, `PHP fatal/parse error rendered by ${url}`).not.toMatch(FATAL_ERROR_PATTERN);
	expect(
		markup,
		`PHP warning/notice/deprecation from this plugin rendered by ${url}`
	).not.toMatch(PLUGIN_DIAGNOSTIC_PATTERN);
}

module.exports = {
	wpCli,
	seedFixtures,
	loginAsAdmin,
	getRestNonce,
	FATAL_ERROR_PATTERN,
	PLUGIN_PHP_FILES,
	PLUGIN_DIAGNOSTIC_PATTERN,
	expectNoPhpDiagnostics,
};
