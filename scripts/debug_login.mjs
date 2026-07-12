import puppeteer from 'puppeteer';

const BASE = 'http://localhost/OOP/multiHotel/public';

const browser = await puppeteer.launch({
  executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
  headless: true,
  defaultViewport: { width: 1440, height: 900 },
  args: ['--no-sandbox', '--disable-setuid-sandbox'],
});

const page = await browser.newPage();

// Test admin login
console.log('Navigating to login…');
await page.goto(`${BASE}/login`, { waitUntil: 'networkidle2', timeout: 30000 });
console.log('Login page URL:', page.url());

await page.type('input[name="email"]', 'admin@hotel.com', { delay: 20 });
await page.type('input[name="password"]', 'password', { delay: 20 });

console.log('Submitting…');
await page.click('button[type="submit"]');
try {
  await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 30000 });
} catch(e) {
  console.log('Nav error:', e.message.slice(0, 80));
}
console.log('Post-login URL:', page.url());

// Check for errors on page
const bodyText = await page.evaluate(() => document.body.innerText.slice(0, 300));
console.log('Page text:', bodyText);

await browser.close();
