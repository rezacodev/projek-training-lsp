import os
import fitz

PAGES = [
    {
        "title": "Dokumentasi Aplikasi LSP - Sistem Inventaris & Penjualan (POS)",
        "subtitle": "1. Halaman Login - Otentikasi Pengguna & Akses Masuk Administrator",
        "image": "docs/screenshots/01_login.png",
    },
    {
        "title": "Dokumentasi Aplikasi LSP - Sistem Inventaris & Penjualan (POS)",
        "subtitle": "2. Beranda / Dashboard - Ringkasan Statistik Produk, Pelanggan, Transaksi, & Pendapatan",
        "image": "docs/screenshots/02_beranda.png",
    },
    {
        "title": "Dokumentasi Aplikasi LSP - Sistem Inventaris & Penjualan (POS)",
        "subtitle": "3. Inventaris Produk - Katalog Produk, Filter Kategori, Stok Tersedia, & Aksi Manajemen",
        "image": "docs/screenshots/03_produk_index.png",
    },
    {
        "title": "Dokumentasi Aplikasi LSP - Sistem Inventaris & Penjualan (POS)",
        "subtitle": "4. Tambah Produk Baru - Formulir Input Identitas, Kategori, Harga, Stok, & Upload Gambar",
        "image": "docs/screenshots/04_produk_create.png",
    },
    {
        "title": "Dokumentasi Aplikasi LSP - Sistem Inventaris & Penjualan (POS)",
        "subtitle": "5. Edit Produk - Formulir Pembaruan Data Produk, Preview Gambar Eksisting, & Perubahan Stok",
        "image": "docs/screenshots/05_produk_edit.png",
    },
    {
        "title": "Dokumentasi Aplikasi LSP - Sistem Inventaris & Penjualan (POS)",
        "subtitle": "6. Manajemen Pengguna - Kelola Akun Staf Sistem (Administrator, Kasir, Gudang)",
        "image": "docs/screenshots/06_pengguna.png",
    },
    {
        "title": "Dokumentasi Aplikasi LSP - Sistem Inventaris & Penjualan (POS)",
        "subtitle": "7. Manajemen Pelanggan - Data Master Pelanggan, Kontak Telepon, & Alamat Pembeli",
        "image": "docs/screenshots/07_pelanggan.png",
    },
    {
        "title": "Dokumentasi Aplikasi LSP - Sistem Inventaris & Penjualan (POS)",
        "subtitle": "8. Riwayat Transaksi - Daftar Transaksi Penjualan, Status Pembayaran, & Detail Invoice",
        "image": "docs/screenshots/08_transaksi_index.png",
    },
    {
        "title": "Dokumentasi Aplikasi LSP - Sistem Inventaris & Penjualan (POS)",
        "subtitle": "9. Kasir POS (Point of Sale) - Antarmuka Kasir Penjualan, Keranjang Belanja, Diskon, Pajak, & Pembayaran",
        "image": "docs/screenshots/09_pos_kasir.png",
    },
    {
        "title": "Dokumentasi Aplikasi LSP - Sistem Inventaris & Penjualan (POS)",
        "subtitle": "10. Keamanan Sesi - Dialog Modal Konfirmasi Keluar Sistem (Logout)",
        "image": "docs/screenshots/10_logout_modal.png",
    },
]

def build_pdf(output_path, pages_list, task_title=None):
    doc = fitz.open()
    total_pages = len(pages_list)

    for idx, p in enumerate(pages_list, 1):
        # A4 Landscape: 842 x 595
        page = doc.new_page(width=842, height=595)

        title = task_title if task_title else p["title"]
        subtitle = p["subtitle"]
        footer = f"Screenshot documentation - page {idx} of {total_pages}"

        # Header Title
        page.insert_text(
            fitz.Point(28, 25),
            title,
            fontname="helv",
            fontsize=16,
            color=(0.12, 0.16, 0.24), # dark navy
        )

        # Header Subtitle
        page.insert_text(
            fitz.Point(28, 44),
            subtitle,
            fontname="helv",
            fontsize=10,
            color=(0.35, 0.40, 0.48), # muted gray
        )

        # Footer
        page.insert_text(
            fitz.Point(28, 578),
            footer,
            fontname="helv",
            fontsize=8,
            color=(0.50, 0.55, 0.60),
        )

        # Decorative subtle line above footer
        page.draw_line(
            fitz.Point(28, 566),
            fitz.Point(814, 566),
            color=(0.88, 0.90, 0.94),
            width=0.75,
        )

        # Decorative subtle line below header
        page.draw_line(
            fitz.Point(28, 55),
            fitz.Point(814, 55),
            color=(0.88, 0.90, 0.94),
            width=0.75,
        )

        # Image container rect: x0=45, y0=68, x1=797, y1=552
        image_rect = fitz.Rect(45, 68, 797, 552)

        # Insert screenshot with keep_proportion
        img_path = p["image"]
        if os.path.exists(img_path):
            page.insert_image(image_rect, filename=img_path, keep_proportion=True)

    doc.save(output_path)
    print(f"Saved: {output_path} ({len(doc)} pages)")

if __name__ == "__main__":
    # 1. Detailed Comprehensive Documentation (10 pages)
    build_pdf("docs/dokumentasi-aplikasi-lsp.pdf", PAGES)

    # 2. Updated Task 2 Product Inventory PDF (4 pages, matching exact original name & scope, but refreshed with new admin topbar)
    product_pages = [
        {
            "subtitle": "Beranda - ringkasan dan pintasan inventaris",
            "image": "docs/screenshots/02_beranda.png",
        },
        {
            "subtitle": "Daftar produk - katalog, stok, dan aksi CRUD",
            "image": "docs/screenshots/03_produk_index.png",
        },
        {
            "subtitle": "Tambah produk - formulir input inventaris",
            "image": "docs/screenshots/04_produk_create.png",
        },
        {
            "subtitle": "Edit produk - formulir pembaruan inventaris",
            "image": "docs/screenshots/05_produk_edit.png",
        },
    ]
    build_pdf("docs/tugas-2-produk.pdf", product_pages, task_title="Task 2 - Product Inventory")
