<div class="form-row">
    <div class="form-group col-md-4">
        <label for="sku">SKU</label>
        <input type="text" id="sku" name="sku" value="{{ old('sku', $product->sku ?? '') }}"
            class="form-control @error('sku') is-invalid @enderror" maxlength="40" required>
        @error('sku')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="form-text text-muted">Kode unik untuk membedakan setiap produk.</small>
    </div>
    <div class="form-group col-md-8">
        <label for="name">Nama Produk</label>
        <input type="text" id="name" name="name" value="{{ old('name', $product->name ?? '') }}"
            class="form-control @error('name') is-invalid @enderror" minlength="2" maxlength="120" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-4">
        <label for="category">Kategori</label>
        <input type="text" id="category" name="category"
            value="{{ old('category', $product->category ?? '') }}"
            class="form-control @error('category') is-invalid @enderror" maxlength="80" required
            placeholder="Contoh: Elektronik">
        @error('category')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-md-4">
        <label for="price">Harga (Rp)</label>
        <input type="number" id="price" name="price" value="{{ old('price', $product->price ?? '') }}"
            class="form-control @error('price') is-invalid @enderror" min="0" max="9999999999.99"
            step="0.01" required>
        @error('price')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-md-4">
        <label for="stock">Stok (unit)</label>
        <input type="number" id="stock" name="stock" value="{{ old('stock', $product->stock ?? 0) }}"
            class="form-control @error('stock') is-invalid @enderror" min="0" max="1000000000"
            step="1" required>
        @error('stock')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-group">
    <label for="description">Deskripsi <span class="text-muted">(opsional)</span></label>
    <textarea id="description" name="description" rows="4"
        class="form-control @error('description') is-invalid @enderror" maxlength="2000"
        placeholder="Tuliskan informasi singkat tentang produk">{{ old('description', $product->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="image">Gambar Produk <span class="text-muted">(opsional, JPG/PNG/WEBP maks. 2 MB)</span></label>
    @if (isset($product) && $product->image_url)
        <div class="d-flex align-items-center mb-2">
            <img src="{{ $product->image_url }}" alt="Gambar {{ $product->name }}" class="rounded border mr-3"
                style="width: 96px; height: 72px; object-fit: cover;">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="remove_image" name="remove_image" value="1"
                    @checked(old('remove_image'))>
                <label class="custom-control-label" for="remove_image">Hapus gambar saat ini</label>
            </div>
        </div>
    @endif
    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp"
        class="form-control-file @error('image') is-invalid @enderror">
    @error('image')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
    <small class="form-text text-muted">Gambar baru akan menggantikan gambar yang ada.</small>
</div>

<div class="d-flex flex-wrap">
    <button type="submit" class="btn btn-primary mr-2 mb-2">
        <i class="fas fa-save mr-1"></i> {{ $submitLabel }}
    </button>
    <a href="{{ route('products.index') }}" class="btn btn-light border mb-2">
        <i class="fas fa-list mr-1"></i> Daftar Produk
    </a>
</div>
