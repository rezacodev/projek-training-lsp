@php
    $cartSeed = old('items') ?? (isset($transaction)
        ? $transaction->products
            ->map(fn ($product) => ['product_id' => $product->id, 'quantity' => $product->pivot->quantity])
            ->values()
            ->all()
        : []);
    $cartSeed = is_array($cartSeed) ? array_values($cartSeed) : [];

    $selectedCustomer = (string) old('customer_id', $transaction->customer_id ?? '');
    $discountType = old('discount_type', $transaction->discount_type ?? 'amount');
    $discountValue = old('discount_value', isset($transaction) ? (float) $transaction->discount_value : 0);
    $taxPercent = old('tax_percent', isset($transaction) ? (float) $transaction->tax_percent : 0);
    $paymentMethod = old('payment_method', $transaction->payment_method ?? 'cash');
    $paidAmount = old('paid_amount', isset($transaction) ? (float) $transaction->paid_amount : '');
    $transactionDate = old('transaction_date', isset($transaction) ? $transaction->transaction_date->format('Y-m-d') : now()->format('Y-m-d'));

    $categories = $products->pluck('category')->unique()->sort()->values();
    $productData = $products
        ->map(fn ($product) => [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'category' => $product->category,
            'price' => (float) $product->price,
            'stock' => (int) $product->available_stock,
            'image' => $product->image_url,
        ])
        ->values();
    $methodLabels = ['cash' => 'Tunai', 'transfer' => 'Transfer', 'qris' => 'QRIS', 'debit' => 'Debit'];
    $methodIcons = ['cash' => 'money-bill-wave', 'transfer' => 'university', 'qris' => 'qrcode', 'debit' => 'credit-card'];
@endphp

@push('styles')
    <link href="{{ asset('assets/vendor/select2/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/select2/select2-bootstrap4.min.css') }}" rel="stylesheet">
    <style>
        body.pos-body { background: #eef1f8; }
        .pos-form { display: flex; flex-direction: column; min-height: 100vh; }
        .pos-bar { display: flex; align-items: center; flex-wrap: wrap; gap: .6rem; padding: .45rem .75rem; background: linear-gradient(90deg, #4e73df, #224abe); color: #fff; flex: none; }
        .pos-title { font-weight: 800; white-space: nowrap; }
        .pos-search { flex: 1 1 260px; max-width: 480px; min-width: 200px; }
        .pos-date { width: 142px; }
        .pos-hint { font-size: .72rem; opacity: .85; }
        .pos-main { flex: 1; display: flex; flex-direction: column; gap: .6rem; padding: .6rem; min-height: 0; }
        .pos-panel { background: #fff; border-radius: .6rem; box-shadow: 0 .15rem .75rem rgba(58, 59, 69, .12); }
        .pos-products { display: flex; flex-direction: column; min-width: 0; min-height: 0; padding: .6rem; }
        .pos-cats { display: flex; gap: .35rem; overflow-x: auto; padding-bottom: .5rem; flex: none; }
        .pos-cats .btn { white-space: nowrap; flex: none; }
        .pos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(128px, 1fr)); grid-auto-rows: max-content; gap: .55rem; align-content: start; overflow-y: auto; flex: 1; min-height: 0; max-height: 60vh; }

        .product-tile { display: flex; flex-direction: column; padding: 0; border: 1px solid #e3e6f0; border-radius: .5rem; background: #fff; overflow: hidden; text-align: left; cursor: pointer; transition: all .12s; min-width: 0; }
        .product-tile:hover { border-color: #4e73df; box-shadow: 0 .2rem .6rem rgba(78, 115, 223, .28); transform: translateY(-1px); }
        .product-tile:active { transform: scale(.98); }
        .tile-img { position: relative; display: block; aspect-ratio: 4 / 3; background: #f1f3fa; }
        .tile-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .tile-img .in-cart { position: absolute; top: .3rem; right: .3rem; }
        .tile-img .low-stock { position: absolute; bottom: .3rem; left: .3rem; }
        .thumb-fallback { display: flex; width: 100%; height: 100%; align-items: center; justify-content: center; background: linear-gradient(135deg, #e8ecfb, #cfd8f7); color: #4e73df; font-size: 1.4rem; }
        .tile-body { display: block; padding: .35rem .5rem .45rem; }
        .tile-name { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; font-size: .78rem; font-weight: 700; color: #3a3b45; line-height: 1.15; min-height: 2.3em; }
        .tile-meta { display: flex; justify-content: space-between; align-items: baseline; margin-top: .2rem; }
        .tile-price { color: #4e73df; font-weight: 800; font-size: .82rem; }
        .tile-stock { font-size: .68rem; color: #858796; }

        .pos-cart { display: flex; flex-direction: column; min-height: 0; }
        .cart-head { display: flex; align-items: center; gap: .35rem; padding: .5rem .6rem; border-bottom: 1px solid #eceef6; flex: none; }
        .cart-body { flex: 1 1 auto; min-height: 120px; max-height: 280px; overflow-y: auto; position: relative; }
        .cart-empty { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #b7b9cc; text-align: center; font-size: .85rem; pointer-events: none; }
        .cart-row { display: flex; gap: .5rem; padding: .4rem .6rem; border-bottom: 1px solid #eceef6; }
        .cart-thumb { width: 46px; height: 46px; border-radius: .4rem; object-fit: cover; flex: none; overflow: hidden; }
        .cart-thumb.thumb-fallback { font-size: 1.1rem; }
        .cart-info { flex: 1; min-width: 0; }
        .cart-name { font-size: .82rem; font-weight: 700; line-height: 1.15; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .cart-price { font-size: .7rem; color: #858796; }
        .cart-line { display: flex; align-items: center; justify-content: space-between; margin-top: .15rem; }
        .qty-group { width: 96px; }
        .cart-sub { font-weight: 800; font-size: .84rem; }
        .cart-foot { padding: .55rem .6rem; border-top: 2px solid #eceef6; flex: none; }
        .sum-lines { display: flex; justify-content: space-between; font-size: .72rem; color: #858796; margin: .3rem 0; }
        .sum-lines b { color: #3a3b45; }
        .pos-total { background: linear-gradient(180deg, #4e73df 10%, #224abe 100%); color: #fff; border-radius: .5rem; padding: .35rem .75rem; display: flex; align-items: center; justify-content: space-between; margin-bottom: .45rem; }
        .pos-total .amount { font-size: 1.7rem; font-weight: 800; letter-spacing: .3px; line-height: 1.2; }
        #payment-methods .btn { padding: .28rem .2rem; font-size: .74rem; }
        .quick-pay .btn { font-size: .72rem; padding: .2rem 0; }
        .balance-row { display: flex; align-items: center; justify-content: space-between; margin: .35rem 0; }
        .pos-toast { position: fixed; left: 50%; bottom: 1.2rem; transform: translateX(-50%); z-index: 1060; box-shadow: 0 .4rem 1rem rgba(0, 0, 0, .25); }

        .pos-form .select2-container--bootstrap4 .select2-selection--single { height: 31px !important; font-size: .85rem; }
        .pos-form .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered { line-height: 29px; }
        .pos-form .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow { height: 29px; top: 0; }
        .pos-form .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow b { top: 50%; margin-top: -2px; }
        .sel-thumb { width: 36px; height: 28px; border-radius: .25rem; object-fit: cover; flex: none; overflow: hidden; }
        .sel-thumb.thumb-fallback { font-size: .8rem; }

        @media (min-width: 992px) and (min-height: 600px) {
            html, body.pos-body { height: 100%; overflow: hidden; }
            .pos-form { height: 100vh; min-height: 0; }
            .pos-main { flex-direction: row; overflow: hidden; }
            .pos-products { flex: 1 1 0; }
            .pos-grid { max-height: none; }
            .pos-cart { flex: 0 0 clamp(380px, 31vw, 480px); }
            .cart-body { flex: 1 1 0; max-height: none; }
        }

        @media (min-width: 992px) and (max-height: 800px) {
            .pos-total .amount { font-size: 1.4rem; }
            .cart-foot { padding: .4rem .5rem; }
            .sum-lines { margin: .2rem 0; }
            .balance-row { margin: .25rem 0; }
        }
    </style>
@endpush

<header class="pos-bar">
    <a href="{{ route('home') }}" class="btn btn-sm btn-light" title="Kembali ke beranda">
        <i class="fas fa-arrow-left"></i><span class="d-none d-md-inline ml-1">Beranda</span>
    </a>
    <div class="pos-title"><i class="fas fa-cash-register mr-1"></i>{{ $heading }}</div>
    <div class="pos-search">
        <label for="product-search" class="sr-only">Cari produk</label>
        <select id="product-search" style="width: 100%">
            <option></option>
            @foreach ($products as $product)
                <option value="{{ $product->id }}"
                    data-search="{{ strtolower($product->name.' '.$product->sku.' '.$product->category) }}">
                    {{ $product->name }} ({{ $product->sku }}) · Rp{{ number_format((float) $product->price, 0, ',', '.') }} · stok {{ $product->available_stock }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="ml-auto d-flex align-items-center">
        <span class="pos-hint d-none d-xl-inline mr-3">F2 Cari · F9 Bayar</span>
        <label for="transaction_date" class="sr-only">Tanggal</label>
        <input type="date" id="transaction_date" name="transaction_date" max="{{ now()->format('Y-m-d') }}"
            value="{{ $transactionDate }}"
            class="form-control form-control-sm pos-date @error('transaction_date') is-invalid @enderror" required>
        <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-light ml-2" title="Riwayat transaksi">
            <i class="fas fa-history"></i><span class="d-none d-md-inline ml-1">Riwayat</span>
        </a>
    </div>
</header>

<main class="pos-main">
    <section class="pos-products pos-panel">
        <div class="pos-cats" id="category-filter">
            <button type="button" class="btn btn-sm btn-primary category-pill" data-category="">Semua</button>
            @foreach ($categories as $category)
                <button type="button" class="btn btn-sm btn-outline-primary category-pill"
                    data-category="{{ $category }}">{{ $category }}</button>
            @endforeach
        </div>

        <div class="pos-grid" id="product-grid">
            @foreach ($products as $product)
                <button type="button" class="product-tile" data-id="{{ $product->id }}"
                    data-category="{{ $product->category }}" aria-label="Tambah {{ $product->name }}">
                    <span class="tile-img">
                        @if ($product->image_url)
                            <img src="{{ $product->image_url }}" alt="" loading="lazy">
                        @else
                            <span class="thumb-fallback"><i class="fas fa-box"></i></span>
                        @endif
                        <span class="badge badge-success in-cart d-none"></span>
                        @if ($product->available_stock <= 5)
                            <span class="badge badge-danger low-stock">Sisa {{ $product->available_stock }}</span>
                        @endif
                    </span>
                    <span class="tile-body">
                        <span class="tile-name">{{ $product->name }}</span>
                        <span class="tile-meta">
                            <span class="tile-price">Rp{{ number_format((float) $product->price, 0, ',', '.') }}</span>
                            <span class="tile-stock">Stok {{ $product->available_stock }}</span>
                        </span>
                    </span>
                </button>
            @endforeach
        </div>

        @if ($products->isEmpty())
            <div class="text-center text-muted py-5">
                <i class="fas fa-box-open fa-2x mb-2"></i>
                <div>Belum ada produk dengan stok tersedia.</div>
                <a href="{{ route('products.create') }}" class="btn btn-sm btn-primary mt-2">Tambah produk</a>
            </div>
        @endif
    </section>

    <aside class="pos-cart pos-panel">
        <div class="cart-head">
            <div class="flex-grow-1">
                <label for="customer_id" class="sr-only">Pelanggan</label>
                <select id="customer_id" name="customer_id" style="width: 100%">
                    <option value="">Pelanggan Umum (tanpa data)</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}"
                            data-search="{{ strtolower($customer->name.' '.$customer->phone.' '.$customer->email) }}"
                            @selected($selectedCustomer === (string) $customer->id)>
                            {{ $customer->name }} · {{ $customer->phone }}
                        </option>
                    @endforeach
                </select>
            </div>
            <a href="{{ route('customers.create') }}" target="_blank" rel="noopener"
                class="btn btn-sm btn-outline-primary" title="Tambah pelanggan baru" aria-label="Tambah pelanggan baru">
                <i class="fas fa-user-plus"></i>
            </a>
            <button type="button" class="btn btn-sm btn-outline-danger" id="clear-cart" title="Kosongkan keranjang"
                aria-label="Kosongkan keranjang">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>

        <div class="cart-body">
            <div id="cart-items"></div>
            <div class="cart-empty" id="cart-empty">
                <i class="fas fa-shopping-basket fa-2x mb-2"></i>
                <div>Keranjang kosong.<br>Klik produk atau tekan F2 untuk mencari.</div>
            </div>
        </div>

        <div class="cart-foot">
            <div class="form-row mb-1">
                <div class="col-7">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text">Diskon</span></div>
                        <input type="number" min="0" step="any" id="discount_value" name="discount_value"
                            value="{{ $discountValue }}" aria-label="Nilai diskon"
                            class="form-control text-right @error('discount_value') is-invalid @enderror">
                        <div class="input-group-append">
                            <select name="discount_type" id="discount_type" class="custom-select custom-select-sm"
                                style="max-width: 62px" aria-label="Jenis diskon">
                                <option value="amount" @selected($discountType === 'amount')>Rp</option>
                                <option value="percent" @selected($discountType === 'percent')>%</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-5">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text">PPN</span></div>
                        <input type="number" min="0" max="100" step="any" id="tax_percent" name="tax_percent"
                            value="{{ $taxPercent }}" aria-label="Persentase pajak"
                            class="form-control text-right @error('tax_percent') is-invalid @enderror">
                        <div class="input-group-append"><span class="input-group-text">%</span></div>
                    </div>
                </div>
            </div>

            <div class="sum-lines">
                <span>Subtotal <b id="sum-subtotal">Rp0</b></span>
                <span>Diskon <b id="sum-discount">- Rp0</b></span>
                <span>Pajak <b id="sum-tax">+ Rp0</b></span>
            </div>

            <div class="pos-total">
                <span class="font-weight-bold text-uppercase small">Total</span>
                <span class="amount" id="sum-total">Rp0</span>
            </div>

            <span class="sr-only">Metode Pembayaran</span>
            <div class="btn-group btn-group-toggle w-100 mb-1" data-toggle="buttons" id="payment-methods">
                @foreach ($methodLabels as $key => $label)
                    <label class="btn btn-outline-primary {{ $paymentMethod === $key ? 'active' : '' }}">
                        <input type="radio" name="payment_method" value="{{ $key }}" autocomplete="off"
                            @checked($paymentMethod === $key)>
                        <i class="fas fa-{{ $methodIcons[$key] }} mr-1"></i>{{ $label }}
                    </label>
                @endforeach
            </div>

            <div class="input-group mb-1">
                <div class="input-group-prepend"><span class="input-group-text font-weight-bold">Bayar Rp</span></div>
                <input type="number" min="0" step="any" id="paid_amount" name="paid_amount"
                    value="{{ $paidAmount }}" placeholder="0" aria-label="Jumlah bayar"
                    class="form-control text-right font-weight-bold @error('paid_amount') is-invalid @enderror">
            </div>
            <div class="btn-group w-100 quick-pay" id="quick-pay">
                <button type="button" class="btn btn-success" data-pay="exact">Uang Pas</button>
                @foreach ([20000 => '20rb', 50000 => '50rb', 100000 => '100rb', 200000 => '200rb'] as $nominal => $label)
                    <button type="button" class="btn btn-outline-secondary" data-pay="{{ $nominal }}">{{ $label }}</button>
                @endforeach
            </div>

            <div class="balance-row">
                <span class="font-weight-bold small" id="balance-label">Kembalian</span>
                <span class="h6 mb-0 font-weight-bold" id="balance-amount">Rp0</span>
                <span class="badge badge-secondary px-2 py-1" id="payment-status">Belum Dibayar</span>
            </div>

            <label for="notes" class="sr-only">Catatan</label>
            <input type="text" id="notes" name="notes" maxlength="500" value="{{ old('notes', $transaction->notes ?? '') }}"
                class="form-control form-control-sm mb-2 @error('notes') is-invalid @enderror"
                placeholder="Catatan (opsional)">

            <button type="submit" class="btn btn-primary btn-block" id="submit-button">
                <i class="fas fa-cash-register mr-1"></i> {{ $submitLabel }}
            </button>
        </div>
    </aside>
</main>

<div id="pos-notice" class="alert alert-warning py-2 px-3 pos-toast d-none" role="alert"></div>

@if ($errors->any() || session('error'))
    <div class="modal fade" id="pos-errors" tabindex="-1" role="dialog" aria-labelledby="pos-errors-title"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white py-2">
                    <h6 class="modal-title" id="pos-errors-title">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Transaksi belum dapat disimpan
                    </h6>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="mb-1 small text-muted">Periksa isian berikut:</p>
                    <ul class="mb-0 pl-3">
                        @if (session('error'))
                            <li>{{ session('error') }}</li>
                        @endif
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Periksa kembali</button>
                </div>
            </div>
        </div>
    </div>
@endif

@push('scripts')
    <script src="{{ asset('assets/vendor/select2/select2.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/select2/id.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const catalog = @json($productData).reduce((map, product) => (map[product.id] = product, map), {});
            const seed = @json($cartSeed);
            const money = (value) => 'Rp' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Math.round(value));
            const escapeHtml = (text) => String(text == null ? '' : text).replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char]));

            const form = document.querySelector('.pos-form');
            const cartEl = document.getElementById('cart-items');
            const emptyEl = document.getElementById('cart-empty');
            const noticeEl = document.getElementById('pos-notice');
            const paidEl = document.getElementById('paid_amount');
            const discountValueEl = document.getElementById('discount_value');
            const discountTypeEl = document.getElementById('discount_type');
            const taxEl = document.getElementById('tax_percent');
            let cart = [];
            let total = 0;
            let noticeTimer;

            function thumbHtml(product, className) {
                return product.image
                    ? '<img class="' + className + '" src="' + escapeHtml(product.image) + '" alt="">'
                    : '<span class="' + className + ' thumb-fallback"><i class="fas fa-box"></i></span>';
            }

            function textMatcher(params, data) {
                if (!params.term || params.term.trim() === '') return data;
                if (typeof data.text === 'undefined') return null;
                const haystack = ((data.element && data.element.dataset.search) || data.text).toLowerCase();
                return params.term.toLowerCase().split(/\s+/).every((term) => haystack.includes(term)) ? data : null;
            }

            $('#customer_id').select2({ theme: 'bootstrap4', language: 'id', width: '100%', matcher: textMatcher });
            $('#product-search').select2({
                theme: 'bootstrap4', language: 'id', width: '100%', matcher: textMatcher,
                placeholder: 'Cari produk (nama / SKU / kategori)...', allowClear: true,
                templateResult: function (data) {
                    const product = catalog[data.id];
                    if (!data.id || !product) return data.text;
                    return $('<div class="d-flex align-items-center"></div>')
                        .append(thumbHtml(product, 'sel-thumb'))
                        .append($('<div class="ml-2"></div>')
                            .append($('<div class="font-weight-bold"></div>').text(product.name))
                            .append($('<div class="small text-muted"></div>').text(product.sku + ' · ' + money(product.price) + ' · stok ' + product.stock)));
                },
            }).on('select2:select', function (event) {
                addToCart(Number(event.params.data.id));
                $(this).val(null).trigger('change');
            });

            function notify(message) {
                noticeEl.textContent = message;
                noticeEl.classList.remove('d-none');
                clearTimeout(noticeTimer);
                noticeTimer = setTimeout(() => noticeEl.classList.add('d-none'), 3000);
            }

            function addToCart(id) {
                const product = catalog[id];
                if (!product) return;
                const line = cart.find((item) => item.id === id);
                if (line) {
                    if (line.quantity >= product.stock) return notify('Stok ' + product.name + ' maksimal ' + product.stock + ' unit.');
                    line.quantity++;
                } else {
                    cart.push({ id: id, quantity: 1 });
                }
                renderCart();
                cartEl.parentElement.scrollTop = cartEl.parentElement.scrollHeight;
            }

            function renderCart() {
                emptyEl.classList.toggle('d-none', cart.length > 0);
                cartEl.innerHTML = cart.map(function (line, index) {
                    const product = catalog[line.id];
                    return '<div class="cart-row" data-id="' + line.id + '">'
                        + '<input type="hidden" name="items[' + index + '][product_id]" value="' + line.id + '">'
                        + '<input type="hidden" class="qty-hidden" name="items[' + index + '][quantity]" value="' + line.quantity + '">'
                        + thumbHtml(product, 'cart-thumb')
                        + '<div class="cart-info">'
                        + '<div class="d-flex justify-content-between align-items-start">'
                        + '<div class="cart-name" title="' + escapeHtml(product.name) + '">' + escapeHtml(product.name) + '</div>'
                        + '<button type="button" class="btn btn-link text-danger p-0 ml-1 remove-line" aria-label="Hapus ' + escapeHtml(product.name) + '"><i class="fas fa-times"></i></button>'
                        + '</div>'
                        + '<div class="cart-price">' + money(product.price) + ' · stok ' + product.stock + '</div>'
                        + '<div class="cart-line">'
                        + '<div class="input-group input-group-sm qty-group">'
                        + '<div class="input-group-prepend"><button type="button" class="btn btn-outline-secondary qty-minus" aria-label="Kurangi">&minus;</button></div>'
                        + '<input type="number" min="1" max="' + product.stock + '" class="form-control text-center px-0 qty-input" value="' + line.quantity + '" aria-label="Jumlah">'
                        + '<div class="input-group-append"><button type="button" class="btn btn-outline-secondary qty-plus" aria-label="Tambah">+</button></div>'
                        + '</div>'
                        + '<span class="cart-sub line-subtotal">' + money(product.price * line.quantity) + '</span>'
                        + '</div></div></div>';
                }).join('');

                document.querySelectorAll('.product-tile').forEach(function (tile) {
                    const line = cart.find((item) => item.id === Number(tile.dataset.id));
                    const badge = tile.querySelector('.in-cart');
                    badge.classList.toggle('d-none', !line);
                    badge.textContent = line ? line.quantity + '×' : '';
                });

                updateTotals();
            }

            function updateTotals() {
                const subtotal = cart.reduce((sum, line) => sum + catalog[line.id].price * line.quantity, 0);
                const discountValue = Math.max(parseFloat(discountValueEl.value) || 0, 0);
                let discount = discountTypeEl.value === 'percent' ? subtotal * discountValue / 100 : discountValue;
                discount = Math.min(discount, subtotal);
                const taxable = subtotal - discount;
                const tax = taxable * Math.min(Math.max(parseFloat(taxEl.value) || 0, 0), 100) / 100;
                total = taxable + tax;

                document.getElementById('sum-subtotal').textContent = money(subtotal);
                document.getElementById('sum-discount').textContent = '- ' + money(discount);
                document.getElementById('sum-tax').textContent = '+ ' + money(tax);
                document.getElementById('sum-total').textContent = money(total);

                const paid = Math.max(parseFloat(paidEl.value) || 0, 0);
                const balanceAmount = document.getElementById('balance-amount');
                const status = document.getElementById('payment-status');
                const diff = Math.round((paid - total) * 100) / 100;

                document.getElementById('balance-label').textContent = diff >= 0 ? 'Kembalian' : 'Kurang Bayar';
                balanceAmount.textContent = money(Math.abs(diff));
                balanceAmount.className = 'h6 mb-0 font-weight-bold ' + (diff >= 0 ? 'text-success' : 'text-danger');

                if (cart.length === 0 || (paid <= 0 && total > 0)) {
                    status.textContent = 'Belum Dibayar'; status.className = 'badge badge-secondary px-2 py-1';
                } else if (diff >= 0) {
                    status.textContent = 'Lunas'; status.className = 'badge badge-success px-2 py-1';
                } else {
                    status.textContent = 'Sebagian'; status.className = 'badge badge-warning px-2 py-1';
                }
            }

            cartEl.addEventListener('click', function (event) {
                const row = event.target.closest('.cart-row');
                if (!row) return;
                const line = cart.find((item) => item.id === Number(row.dataset.id));
                const product = catalog[line.id];

                if (event.target.closest('.remove-line')) {
                    cart = cart.filter((item) => item !== line);
                } else if (event.target.closest('.qty-plus')) {
                    if (line.quantity >= product.stock) return notify('Stok ' + product.name + ' maksimal ' + product.stock + ' unit.');
                    line.quantity++;
                } else if (event.target.closest('.qty-minus')) {
                    if (line.quantity <= 1) { cart = cart.filter((item) => item !== line); } else { line.quantity--; }
                } else {
                    return;
                }
                renderCart();
            });

            cartEl.addEventListener('input', function (event) {
                if (!event.target.classList.contains('qty-input')) return;
                const row = event.target.closest('.cart-row');
                const line = cart.find((item) => item.id === Number(row.dataset.id));
                const product = catalog[line.id];
                let quantity = parseInt(event.target.value, 10) || 0;

                if (quantity > product.stock) {
                    quantity = product.stock;
                    event.target.value = quantity;
                    notify('Stok ' + product.name + ' maksimal ' + product.stock + ' unit.');
                }

                line.quantity = Math.max(quantity, 1);
                row.querySelector('.qty-hidden').value = line.quantity;
                row.querySelector('.line-subtotal').textContent = money(product.price * line.quantity);
                updateTotals();
            });

            cartEl.addEventListener('change', function (event) {
                if (event.target.classList.contains('qty-input')) renderCart();
            });

            document.getElementById('category-filter').addEventListener('click', function (event) {
                const pill = event.target.closest('.category-pill');
                if (!pill) return;
                document.querySelectorAll('.category-pill').forEach(function (button) {
                    button.classList.toggle('btn-primary', button === pill);
                    button.classList.toggle('btn-outline-primary', button !== pill);
                });
                document.querySelectorAll('.product-tile').forEach(function (tile) {
                    tile.classList.toggle('d-none', Boolean(pill.dataset.category) && tile.dataset.category !== pill.dataset.category);
                });
            });

            document.getElementById('product-grid').addEventListener('click', function (event) {
                const tile = event.target.closest('.product-tile');
                if (tile) addToCart(Number(tile.dataset.id));
            });

            document.getElementById('quick-pay').addEventListener('click', function (event) {
                const button = event.target.closest('[data-pay]');
                if (!button) return;
                paidEl.value = button.dataset.pay === 'exact' ? Math.round(total * 100) / 100 : button.dataset.pay;
                updateTotals();
            });

            document.getElementById('payment-methods').addEventListener('change', function (event) {
                if (event.target.value !== 'cash' && (parseFloat(paidEl.value) || 0) === 0 && total > 0) {
                    paidEl.value = Math.round(total * 100) / 100;
                    updateTotals();
                }
            });

            document.getElementById('clear-cart').addEventListener('click', function () {
                cart = [];
                renderCart();
            });

            [paidEl, discountValueEl, discountTypeEl, taxEl].forEach((element) => {
                element.addEventListener('input', updateTotals);
                element.addEventListener('change', updateTotals);
            });

            form.addEventListener('submit', function (event) {
                if (cart.length === 0) {
                    event.preventDefault();
                    notify('Tambahkan minimal satu produk ke keranjang.');
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'F2') {
                    event.preventDefault();
                    $('#product-search').select2('open');
                } else if (event.key === 'F9') {
                    event.preventDefault();
                    document.getElementById('submit-button').click();
                }
            });

            seed.forEach(function (item) {
                const product = catalog[Number(item.product_id)];
                const quantity = parseInt(item.quantity, 10) || 1;
                if (product && !cart.some((line) => line.id === product.id)) {
                    cart.push({ id: product.id, quantity: Math.min(Math.max(quantity, 1), product.stock) });
                }
            });

            renderCart();

            if (document.getElementById('pos-errors')) {
                $('#pos-errors').modal('show');
            }
        });
    </script>
@endpush
