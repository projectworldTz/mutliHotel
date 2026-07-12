/**
 * Portfolio Screenshot Script
 * Captures all system pages for showcase/portfolio use.
 *
 * Usage:  node scripts/screenshots.mjs
 * Output: portfolio_screenshots/
 */

import puppeteer from 'puppeteer';
import fs from 'fs';
import path from 'path';

// ─── Config ──────────────────────────────────────────────────────────────────

const BASE        = 'http://localhost/OOP/multiHotel/public';
const OUT_DIR     = path.resolve('portfolio_screenshots');
const VIEWPORT    = { width: 1440, height: 900 };
const DELAY_MS    = 1200;   // wait after navigation for animations to settle
const SCROLL_WAIT = 600;    // wait after scrolling

const CREDENTIALS = {
  admin:        { email: 'admin@hotel.com',           password: 'password' },
  owner:        { email: 'owner@hotel.com',            password: 'password' },
  receptionist: { email: 'informtech2001@gmail.com',   password: 'password' },
  guest:        { email: 'guest@hotel.com',            password: 'password' },
};

// ─── Page definitions ─────────────────────────────────────────────────────────
// Each entry: { file, url, role (null = public), section, label }

const PAGES = [

  // ── Public / Guest ──────────────────────────────────────────────────────────
  { section: 'Public',    file: '01_home',                   url: '/',                                                          role: null         },
  { section: 'Public',    file: '02_hotel_listing',          url: '/hotels/kilimanjaro-grand-hotel',                            role: null         },
  { section: 'Public',    file: '03_hotel_tranquiloo',       url: '/hotels/tranquiloo',                                         role: null         },
  { section: 'Public',    file: '04_room_detail',            url: '/hotels/kilimanjaro-grand-hotel/rooms/deluxe-suite',         role: null         },
  { section: 'Public',    file: '05_blog',                   url: '/blog',                                                      role: null         },
  { section: 'Auth',      file: '06_login',                  url: '/login',                                                     role: null         },
  { section: 'Auth',      file: '07_register',               url: '/register',                                                  role: null         },

  // ── Guest account ───────────────────────────────────────────────────────────
  { section: 'Guest',     file: '08_guest_bookings',         url: '/bookings',                                                  role: 'guest'      },
  { section: 'Guest',     file: '09_guest_account',          url: '/account',                                                   role: 'guest'      },
  { section: 'Guest',     file: '10_guest_favorites',        url: '/favorites',                                                 role: 'guest'      },

  // ── Admin ────────────────────────────────────────────────────────────────────
  { section: 'Admin',     file: '11_admin_dashboard',        url: '/admin',                                                     role: 'admin'      },
  { section: 'Admin',     file: '12_admin_hotels',           url: '/admin/hotels',                                              role: 'admin'      },
  { section: 'Admin',     file: '13_admin_hotel_detail',     url: '/admin/hotels/1',                                            role: 'admin'      },
  { section: 'Admin',     file: '14_admin_hotel_features',   url: '/admin/hotels/1?tab=features',                               role: 'admin'      },
  { section: 'Admin',     file: '15_admin_users',            url: '/admin/users',                                               role: 'admin'      },
  { section: 'Admin',     file: '16_admin_bookings',         url: '/admin/bookings',                                            role: 'admin'      },
  { section: 'Admin',     file: '17_admin_reports_revenue',  url: '/admin/reports/revenue',                                     role: 'admin'      },
  { section: 'Admin',     file: '18_admin_reports_occupancy',url: '/admin/reports/occupancy',                                   role: 'admin'      },
  { section: 'Admin',     file: '19_admin_coupons',          url: '/admin/coupons',                                             role: 'admin'      },
  { section: 'Admin',     file: '20_admin_settings',         url: '/admin/settings',                                            role: 'admin'      },
  { section: 'Admin',     file: '21_admin_audit_logs',       url: '/admin/audit-logs',                                          role: 'admin'      },

  // ── Owner ────────────────────────────────────────────────────────────────────
  { section: 'Owner',     file: '22_owner_dashboard',        url: '/owner/dashboard',                                           role: 'owner'      },
  { section: 'Owner',     file: '23_owner_hotels',           url: '/owner/hotels',                                              role: 'owner'      },
  { section: 'Owner',     file: '24_owner_hotel_detail',     url: '/owner/hotels/1',                                            role: 'owner'      },
  { section: 'Owner',     file: '25_owner_analytics',        url: '/owner/hotels/1?tab=analytics',                              role: 'owner'      },
  { section: 'Owner',     file: '26_owner_bookings',         url: '/owner/hotels/1?tab=bookings',                               role: 'owner'      },
  { section: 'Owner',     file: '27_owner_rooms',            url: '/owner/hotels/1?tab=rooms',                                  role: 'owner'      },
  { section: 'Owner',     file: '28_owner_guests',           url: '/owner/hotels/1?tab=guests',                                 role: 'owner'      },
  { section: 'Owner',     file: '29_owner_inventory',        url: '/owner/hotels/1/inventory',                                  role: 'owner'      },
  { section: 'Owner',     file: '30_owner_housekeeping',     url: '/owner/hotels/1/housekeeping',                               role: 'owner'      },
  { section: 'Owner',     file: '31_owner_cancellations',    url: '/owner/hotels/1/cancellation-approvals',                     role: 'owner'      },

  // ── Receptionist ────────────────────────────────────────────────────────────
  { section: 'Receptionist', file: '32_rec_dashboard',       url: '/receptionist/dashboard',                                    role: 'receptionist' },
  { section: 'Receptionist', file: '33_rec_bookings',        url: '/receptionist/bookings',                                     role: 'receptionist' },
  { section: 'Receptionist', file: '34_rec_booking_detail',  url: '/receptionist/bookings/BK-20260627-00002',                   role: 'receptionist' },
  { section: 'Receptionist', file: '35_rec_guests',          url: '/receptionist/guests',                                       role: 'receptionist' },
  { section: 'Receptionist', file: '36_rec_housekeeping',    url: '/receptionist/housekeeping',                                 role: 'receptionist' },
  { section: 'Receptionist', file: '37_rec_inventory',       url: '/receptionist/inventory',                                    role: 'receptionist' },
  { section: 'Receptionist', file: '38_rec_cancellations',   url: '/receptionist/cancellation-approvals',                       role: 'receptionist' },
];

// ─── Helpers ─────────────────────────────────────────────────────────────────

function sleep(ms) {
  return new Promise(r => setTimeout(r, ms));
}

async function autoScroll(page) {
  await page.evaluate(async () => {
    await new Promise(resolve => {
      let total = 0;
      const dist = 300;
      const timer = setInterval(() => {
        window.scrollBy(0, dist);
        total += dist;
        if (total >= document.body.scrollHeight) {
          clearInterval(timer);
          window.scrollTo(0, 0);
          resolve();
        }
      }, 100);
    });
  });
  await sleep(SCROLL_WAIT);
}

async function login(page, role) {
  const creds = CREDENTIALS[role];
  await page.goto(`${BASE}/login`, { waitUntil: 'networkidle2' });
  await sleep(500);

  await page.type('input[name="email"]',    creds.email,    { delay: 30 });
  await page.type('input[name="password"]', creds.password, { delay: 30 });
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 15000 }),
    page.click('button[type="submit"]'),
  ]);
  await sleep(600);
}

async function logout(page) {
  try {
    await page.evaluate(() => {
      const form = document.querySelector('form[action*="logout"]');
      if (form) form.submit();
    });
    await page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 8000 }).catch(() => {});
  } catch (_) {}
  await page.goto(`${BASE}/logout`, { waitUntil: 'networkidle0' }).catch(() => {});
  await sleep(400);
}

async function screenshot(page, filePath) {
  // Scroll to trigger lazy-loaded images, then scroll back to top
  await autoScroll(page);
  await page.evaluate(() => window.scrollTo(0, 0));
  await sleep(400);

  await page.screenshot({
    path: filePath,
    fullPage: true,
    type: 'png',
  });
}

// ─── Main ─────────────────────────────────────────────────────────────────────

(async () => {
  // Create output folders
  const sections = [...new Set(PAGES.map(p => p.section))];
  fs.mkdirSync(OUT_DIR, { recursive: true });
  for (const sec of sections) {
    fs.mkdirSync(path.join(OUT_DIR, sec), { recursive: true });
  }

  console.log('\n🚀  Hotel Booking System — Portfolio Screenshot Tool');
  console.log('='.repeat(55));
  console.log(`📁  Output: ${OUT_DIR}`);
  console.log(`📸  Pages:  ${PAGES.length}\n`);

  const browser = await puppeteer.launch({
    executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    headless: true,
    defaultViewport: VIEWPORT,
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--start-maximized'],
  });

  const page = await browser.newPage();
  await page.setViewport(VIEWPORT);

  // Suppress console noise from the app
  page.on('console', () => {});
  page.on('pageerror', () => {});

  let currentRole = null;
  let ok = 0;
  let fail = 0;
  const errors = [];

  for (const p of PAGES) {
    const outFile = path.join(OUT_DIR, p.section, `${p.file}.png`);
    const label   = `[${p.section}] ${p.file}`;

    try {
      // Switch session if role changed
      if (p.role !== currentRole) {
        if (currentRole !== null) await logout(page);
        if (p.role !== null) await login(page, p.role);
        currentRole = p.role;
      }

      await page.goto(`${BASE}${p.url}`, { waitUntil: 'networkidle2', timeout: 20000 });
      await sleep(DELAY_MS);

      // Close any open modals / cookie banners by pressing Escape
      await page.keyboard.press('Escape');
      await sleep(200);

      await screenshot(page, outFile);
      console.log(`  ✅  ${label}`);
      ok++;
    } catch (err) {
      console.error(`  ❌  ${label} — ${err.message.split('\n')[0]}`);
      errors.push({ label, error: err.message.split('\n')[0] });
      fail++;
      // Continue to next page even on error
    }
  }

  await browser.close();

  console.log('\n' + '='.repeat(55));
  console.log(`✅  Done: ${ok} captured   ❌  Failed: ${fail}`);
  if (errors.length) {
    console.log('\nFailed pages:');
    errors.forEach(e => console.log(`  • ${e.label}: ${e.error}`));
  }
  console.log(`\n📂  Screenshots saved to: ${OUT_DIR}`);
})();
