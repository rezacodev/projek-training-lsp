import puppeteer from 'puppeteer-core';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const EDGE_PATH = 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe';
const BASE_URL = 'http://127.0.0.1:8000';
const OUT_DIR = path.resolve(__dirname, '..', 'docs', 'screenshots');

async function main() {
    console.log('Launching Edge from:', EDGE_PATH);
    const browser = await puppeteer.launch({
        executablePath: EDGE_PATH,
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1280,850'],
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1280, height: 850, deviceScaleFactor: 1 });

    // 1. Halaman Login
    console.log('Capturing: 01_login.png');
    await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(OUT_DIR, '01_login.png') });

    // Login process
    console.log('Logging in as admin@example.com...');
    await page.click('button[type="submit"]');
    await page.waitForNavigation({ waitUntil: 'networkidle0' });

    // 2. Beranda / Dashboard
    console.log('Capturing: 02_beranda.png');
    await page.goto(`${BASE_URL}/`, { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(OUT_DIR, '02_beranda.png') });

    // 3. Daftar Produk
    console.log('Capturing: 03_produk_index.png');
    await page.goto(`${BASE_URL}/products`, { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(OUT_DIR, '03_produk_index.png') });

    // 4. Tambah Produk
    console.log('Capturing: 04_produk_create.png');
    await page.goto(`${BASE_URL}/products/create`, { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(OUT_DIR, '04_produk_create.png') });

    // 5. Edit Produk
    console.log('Capturing: 05_produk_edit.png');
    await page.goto(`${BASE_URL}/products/1/edit`, { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(OUT_DIR, '05_produk_edit.png') });

    // 6. Manajemen Pengguna (Users)
    console.log('Capturing: 06_pengguna.png');
    await page.goto(`${BASE_URL}/users`, { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(OUT_DIR, '06_pengguna.png') });

    // 7. Kelola Pelanggan (Customers)
    console.log('Capturing: 07_pelanggan.png');
    await page.goto(`${BASE_URL}/customers`, { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(OUT_DIR, '07_pelanggan.png') });

    // 8. Riwayat Transaksi Penjualan
    console.log('Capturing: 08_transaksi_index.png');
    await page.goto(`${BASE_URL}/transactions`, { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(OUT_DIR, '08_transaksi_index.png') });

    // 9. Kasir POS
    console.log('Capturing: 09_pos_kasir.png');
    await page.goto(`${BASE_URL}/transactions/create`, { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(OUT_DIR, '09_pos_kasir.png') });

    // 10. Modal Konfirmasi Logout
    console.log('Capturing: 10_logout_modal.png');
    await page.goto(`${BASE_URL}/`, { waitUntil: 'networkidle0' });
    await page.evaluate(() => {
        window.$('#logoutModal').modal('show');
    });
    // Wait modal animation
    await new Promise((r) => setTimeout(r, 600));
    await page.screenshot({ path: path.join(OUT_DIR, '10_logout_modal.png') });

    await browser.close();
    console.log('All screenshots captured successfully!');
}

main().catch((err) => {
    console.error('Error:', err);
    process.exit(1);
});
