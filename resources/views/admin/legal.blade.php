@extends('layouts.app')
@section('title', 'Legal Pages')

@section('content')
<x-admin.page-header icon="file-earmark-text" title="Legal Pages"
                     subtitle="Imprint, privacy policy and terms of use, linked in the footer of every page." />

<div class="row g-4">
    {{-- Sidebar: admin sections --}}
    @include('admin._sidebar', ['active' => 'legal'])

    {{-- Main: legal pages form --}}
    <div class="col-12 col-lg-9">
        @if (session('status'))
            <div class="alert alert-success d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="alert alert-light border d-flex">
            <i class="bi bi-info-circle me-2 text-primary"></i>
            <div class="small">
                Pages are written in <strong>Markdown</strong> (<code>## Heading</code>, <code>**bold**</code>,
                <code>- list</code>, <code>[link](https://...)</code>); raw HTML is removed.
                See the <a href="https://docs.github.com/en/get-started/writing-on-github/getting-started-with-writing-and-formatting-on-github/basic-writing-and-formatting-syntax" target="_blank" rel="noopener noreferrer">GitHub Markdown guide <i class="bi bi-box-arrow-up-right"></i></a> for the full syntax.
                YAAMS provides the pages, but you as the operator are responsible for their legal content.
            </div>
        </div>

        <form action="{{ route('admin.legal.update') }}" method="post">
            @csrf
            @method('PUT')

            {{-- Imprint --}}
            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h6 class="fw-semibold mb-0"><i class="bi bi-building me-2 text-primary"></i> Imprint</h6>
                        @if (\App\Support\LegalPage::enabled('imprint'))
                            <a href="{{ route('legal.imprint') }}" target="_blank" class="small text-decoration-none">View page <i class="bi bi-box-arrow-up-right"></i></a>
                        @endif
                    </div>

                    <div class="mb-3">
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="imprint_enabled" id="imprint_on" value="1"
                                   {{ old('imprint_enabled', $settings['imprint_enabled']) === '1' ? 'checked' : '' }}>
                            <label class="btn btn-outline-secondary" for="imprint_on">
                                <i class="bi bi-eye me-1"></i> Published
                            </label>

                            <input type="radio" class="btn-check" name="imprint_enabled" id="imprint_off" value="0"
                                   {{ old('imprint_enabled', $settings['imprint_enabled']) === '0' ? 'checked' : '' }}>
                            <label class="btn btn-outline-secondary" for="imprint_off">
                                <i class="bi bi-eye-slash me-1"></i> Hidden
                            </label>
                        </div>
                        <div class="form-text">Required for operators in e.g. Germany and Austria (name, postal address, contact). Not needed in the US.</div>
                    </div>

                    <textarea name="imprint_content" id="imprint_content" rows="10"
                              class="form-control font-monospace small @error('imprint_content') is-invalid @enderror"
                              placeholder="**Operator**&#10;Jane Doe&#10;Example Street 1&#10;12345 Example City&#10;&#10;E-mail: contact@example.com">{{ old('imprint_content', $settings['imprint_content']) }}</textarea>
                </div>
            </div>

            {{-- Privacy policy --}}
            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h6 class="fw-semibold mb-0"><i class="bi bi-shield-lock me-2 text-info"></i> Privacy Policy</h6>
                        <a href="{{ route('legal.privacy') }}" target="_blank" class="small text-decoration-none">View page <i class="bi bi-box-arrow-up-right"></i></a>
                    </div>

                    @if ($usesDefault)
                        <div class="alert alert-warning small d-flex align-items-start">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <div>
                                The built-in default template is published. It describes what YAAMS stores and is filled
                                with your instance name, support e-mail and log retention - review it and adapt it to your setup.
                            </div>
                        </div>
                    @endif

                    <textarea name="privacy_content" id="privacy_content" rows="16"
                              class="form-control font-monospace small @error('privacy_content') is-invalid @enderror"
                              placeholder="Leave empty to publish the built-in default template.">{{ old('privacy_content', $settings['privacy_content']) }}</textarea>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <div class="form-text mt-0">Always published. Leave empty to use the default template.</div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="load-privacy-template">
                            <i class="bi bi-file-earmark-arrow-down me-1"></i> Load template into editor
                        </button>
                    </div>
                </div>
            </div>

            {{-- Terms of use --}}
            <div class="card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h6 class="fw-semibold mb-0"><i class="bi bi-file-earmark-text me-2 text-warning"></i> Terms of Use</h6>
                        @if (\App\Support\LegalPage::enabled('terms'))
                            <a href="{{ route('legal.terms') }}" target="_blank" class="small text-decoration-none">View page <i class="bi bi-box-arrow-up-right"></i></a>
                        @endif
                    </div>

                    <div class="mb-3">
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="terms_enabled" id="terms_on" value="1"
                                   {{ old('terms_enabled', $settings['terms_enabled']) === '1' ? 'checked' : '' }}>
                            <label class="btn btn-outline-secondary" for="terms_on">
                                <i class="bi bi-eye me-1"></i> Published
                            </label>

                            <input type="radio" class="btn-check" name="terms_enabled" id="terms_off" value="0"
                                   {{ old('terms_enabled', $settings['terms_enabled']) === '0' ? 'checked' : '' }}>
                            <label class="btn btn-outline-secondary" for="terms_off">
                                <i class="bi bi-eye-slash me-1"></i> Hidden
                            </label>
                        </div>
                        <div class="form-text">House rules for your community. When published, new users must accept them at registration.</div>
                    </div>

                    <textarea name="terms_content" id="terms_content" rows="10"
                              class="form-control font-monospace small @error('terms_content') is-invalid @enderror">{{ old('terms_content', $settings['terms_content']) }}</textarea>
                </div>
            </div>

            <button class="btn btn-primary" type="submit">
                <i class="bi bi-check2-circle me-2"></i> Save legal pages
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('load-privacy-template').addEventListener('click', function () {
        const editor = document.getElementById('privacy_content');
        if (editor.value.trim() !== '' && ! confirm('Replace the current privacy policy text with the default template?')) {
            return;
        }
        editor.value = @json(trim($defaultPrivacy));
    });
</script>
@endpush
