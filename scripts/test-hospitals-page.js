const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage();
  const errors = [];
  page.on('pageerror', (err) => errors.push(err.message));
  page.on('console', (msg) => {
    if (msg.type() === 'error') errors.push(`console: ${msg.text()}`);
  });

  await page.goto('http://localhost/1501/hospitals.html', { waitUntil: 'networkidle' });
  await page.waitForTimeout(3000);

  const count = await page.locator('#hospitalListingCount').textContent();
  const cards = await page.locator('.hospital-card').count();
  const emptyVisible = await page.locator('#hospitalListingEmpty').isVisible();
  const emptyText = await page.locator('#hospitalListingEmpty').textContent();

  console.log(JSON.stringify({ count, cards, emptyVisible, emptyText, errors }, null, 2));
  await browser.close();
})().catch((err) => {
  console.error(err);
  process.exit(1);
});
