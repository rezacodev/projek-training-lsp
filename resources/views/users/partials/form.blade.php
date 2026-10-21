<div class="form-group">
    <label for="name">Nama</label>
    <input type="text" id="name" name="name" value="{{ old('name', $user->name ?? '') }}"
        class="form-control @error('name') is-invalid @enderror" minlength="3" maxlength="255" required
        autocomplete="name">
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="{{ old('email', $user->email ?? '') }}"
        class="form-control @error('email') is-invalid @enderror" maxlength="255" required autocomplete="email">
    @error('email')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="password">Kata Sandi{{ isset($user) ? ' (opsional, isi untuk mengganti)' : '' }}</label>
    <input type="password" id="password" name="password"
        class="form-control @error('password') is-invalid @enderror" minlength="6"
        {{ isset($user) ? '' : 'required' }} autocomplete="new-password">
    @error('password')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="d-flex flex-wrap">
    <button type="submit" class="btn btn-primary mr-2 mb-2">
        <i class="fas fa-save mr-1"></i> {{ $submitLabel }}
    </button>
    <a href="{{ route('users.index') }}" class="btn btn-light border mb-2">Batal</a>
</div>
