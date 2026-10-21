<div class="form-row">
    <div class="form-group col-md-6">
        <label for="name">Nama</label>
        <input type="text" id="name" name="name" value="{{ old('name', $customer->name ?? '') }}"
            class="form-control @error('name') is-invalid @enderror" minlength="2" maxlength="120" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-group col-md-6">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email', $customer->email ?? '') }}"
            class="form-control @error('email') is-invalid @enderror" maxlength="255" required>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-group">
    <label for="phone">Nomor Telepon</label>
    <input type="text" id="phone" name="phone" value="{{ old('phone', $customer->phone ?? '') }}"
        class="form-control @error('phone') is-invalid @enderror" maxlength="20" required
        placeholder="Contoh: 081234567890">
    @error('phone')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="address">Alamat <span class="text-muted">(opsional)</span></label>
    <textarea id="address" name="address" rows="3" maxlength="500"
        class="form-control @error('address') is-invalid @enderror">{{ old('address', $customer->address ?? '') }}</textarea>
    @error('address')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="d-flex flex-wrap">
    <button type="submit" class="btn btn-primary mr-2 mb-2">
        <i class="fas fa-save mr-1"></i> {{ $submitLabel }}
    </button>
    <a href="{{ route('customers.index') }}" class="btn btn-light border mb-2">
        <i class="fas fa-list mr-1"></i> Daftar Pelanggan
    </a>
</div>
