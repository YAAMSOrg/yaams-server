{{--
    Aircraft screenshot gallery with community uploads + manager moderation.
    Expects:
      $aircraft     - Aircraft model (with `approvedImages` and `pendingImages` loaded)
      $canModerate  - bool, Manager of the owning airline (approve / delete any / set main)
      $canUpload    - bool, any member of the owning airline (contribute a screenshot)

    Approved shots render in a lightbox (see the @once block at the bottom).
    Pending shots appear in a review queue that only Managers, or the uploader,
    can see.
--}}
@php($userId = auth()->id())
@php($approved = $aircraft->approvedImages)
@php($pending = $canModerate ? $aircraft->pendingImages : $aircraft->pendingImages->where('uploaded_by', $userId))
{{-- On a failed upload, open the Upload tab so the error is visible. --}}
@php($uploadHasError = $errors->has('screenshot'))
<div class="card border-0 shadow-sm mb-4 aircraft-gallery">
    <div class="card-header bg-white border-bottom p-0">
        <ul class="nav nav-tabs card-header-tabs m-0 px-3 pt-2" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $uploadHasError ? '' : 'active' }}" id="gallery-tab-btn-{{ $aircraft->id }}"
                        data-bs-toggle="tab" data-bs-target="#gallery-pane-{{ $aircraft->id }}"
                        type="button" role="tab" aria-controls="gallery-pane-{{ $aircraft->id }}"
                        aria-selected="{{ $uploadHasError ? 'false' : 'true' }}">
                    <i class="bi bi-images me-1"></i> Gallery
                    <span class="badge bg-secondary ms-1 fs-7">{{ $approved->count() }}</span>
                </button>
            </li>
            @if($canUpload)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $uploadHasError ? 'active' : '' }}" id="upload-tab-btn-{{ $aircraft->id }}"
                            data-bs-toggle="tab" data-bs-target="#upload-pane-{{ $aircraft->id }}"
                            type="button" role="tab" aria-controls="upload-pane-{{ $aircraft->id }}"
                            aria-selected="{{ $uploadHasError ? 'true' : 'false' }}">
                        <i class="bi bi-upload me-1"></i> Upload
                    </button>
                </li>
            @endif
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content">
        <div class="tab-pane fade {{ $uploadHasError ? '' : 'show active' }}" id="gallery-pane-{{ $aircraft->id }}"
             role="tabpanel" aria-labelledby="gallery-tab-btn-{{ $aircraft->id }}">
        {{-- Approved gallery --}}
        @if($approved->isEmpty())
            <div class="text-center text-muted py-4">
                <i class="bi bi-image fs-1 text-secondary opacity-50 d-block mb-2"></i>
                No screenshots yet.
                @if($canUpload) Upload the first one from the <strong>Upload</strong> tab. @endif
            </div>
        @else
            <div class="gallery-group">
                @foreach($approved as $img)
                    @php($shotLabel = 'Screenshot '.$loop->iteration.' of '.$loop->count.' of '.$aircraft->registration.($img->is_primary ? ', main livery shot' : ''))
                    <div class="gallery-cell">
                        <div class="position-relative gallery-tile">
                            <button type="button"
                                    class="gallery-thumb"
                                    data-gallery-full="{{ route('aircraft.images.show', [$aircraft->id, $img->id]) }}"
                                    data-gallery-caption="{{ $aircraft->registration }}{{ $img->is_primary ? ' · Main livery shot' : '' }}"
                                    aria-label="Open {{ $shotLabel }}">
                                <img src="{{ route('aircraft.images.show', [$aircraft->id, $img->id]) }}"
                                     alt="{{ $shotLabel }}"
                                     loading="lazy">
                                <span class="gallery-thumb-overlay">
                                    <i class="bi bi-arrows-fullscreen"></i>
                                </span>
                            </button>
                            @if($img->is_primary)
                                <span class="badge bg-primary position-absolute top-0 start-0 m-2 shadow-sm">
                                    <i class="bi bi-star-fill me-1"></i> Main
                                </span>
                            @endif
                            @if($canModerate || $img->uploaded_by === $userId)
                                <div class="d-flex gap-1 mt-2">
                                    @if($canModerate && !$img->is_primary)
                                        <form action="{{ route('aircraft.images.primary', [$aircraft->id, $img->id]) }}" method="post" class="flex-grow-1">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-primary w-100" aria-label="Set {{ $shotLabel }} as main">
                                                <i class="bi bi-star"></i> Main
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('aircraft.images.destroy', [$aircraft->id, $img->id]) }}" method="post"
                                          onsubmit="return confirm('Delete this screenshot?');" class="flex-grow-1">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger w-100" title="Delete" aria-label="Delete {{ $shotLabel }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Pending review queue --}}
        @if($pending->isNotEmpty())
            <div class="mt-4 pt-3 border-top">
                <h3 class="h6 fw-semibold text-secondary mb-3">
                    <i class="bi bi-hourglass-split me-1"></i>
                    {{ $canModerate ? 'Awaiting review' : 'Your uploads awaiting review' }}
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle ms-1">{{ $pending->count() }}</span>
                </h3>
                <div class="gallery-group">
                    @foreach($pending as $img)
                        @php($shotLabel = 'pending screenshot '.$loop->iteration.' of '.$loop->count.' of '.$aircraft->registration.($canModerate && $img->uploader ? ' by '.$img->uploader->name : ''))
                        <div class="gallery-cell">
                            <div class="position-relative gallery-tile">
                                <button type="button"
                                        class="gallery-thumb is-pending"
                                        data-gallery-full="{{ route('aircraft.images.show', [$aircraft->id, $img->id]) }}"
                                        data-gallery-caption="{{ $aircraft->registration }} · pending review"
                                        aria-label="Open {{ $shotLabel }}">
                                    <img src="{{ route('aircraft.images.show', [$aircraft->id, $img->id]) }}"
                                         alt="{{ ucfirst($shotLabel) }}"
                                         loading="lazy">
                                    <span class="gallery-thumb-overlay">
                                        <i class="bi bi-arrows-fullscreen"></i>
                                    </span>
                                </button>
                                <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-2 shadow-sm">
                                    <i class="bi bi-hourglass me-1"></i> Pending
                                </span>
                                @if($canModerate)
                                    <div class="small text-muted mt-2 text-truncate">by {{ $img->uploader?->name ?? 'Unknown' }}</div>
                                @endif
                                <div class="d-flex gap-1 mt-1">
                                    @if($canModerate)
                                        <form action="{{ route('aircraft.images.approve', [$aircraft->id, $img->id]) }}" method="post" class="flex-grow-1">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-success w-100" aria-label="Approve {{ $shotLabel }}">
                                                <i class="bi bi-check-lg"></i> Approve
                                            </button>
                                        </form>
                                    @endif
                                    @if($canModerate || $img->uploaded_by === $userId)
                                        <form action="{{ route('aircraft.images.destroy', [$aircraft->id, $img->id]) }}" method="post"
                                              onsubmit="return confirm('{{ $canModerate ? 'Reject and delete this screenshot?' : 'Delete this screenshot?' }}');"
                                              class="flex-grow-1">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger w-100" aria-label="{{ $canModerate ? 'Reject' : 'Delete' }} {{ $shotLabel }}">
                                                <i class="bi bi-x-lg"></i> {{ $canModerate ? 'Reject' : 'Delete' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        </div>{{-- /gallery pane --}}

        {{-- Upload form (any airline member) --}}
        @if($canUpload)
            <div class="tab-pane fade {{ $uploadHasError ? 'show active' : '' }}" id="upload-pane-{{ $aircraft->id }}"
                 role="tabpanel" aria-labelledby="upload-tab-btn-{{ $aircraft->id }}">
                <form action="{{ route('aircraft.images.store', $aircraft->id) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <label for="screenshot" class="form-label fw-semibold">Upload a screenshot</label>
                    <div class="input-group">
                        <input type="file" name="screenshot" id="screenshot" accept="image/jpeg,image/png,image/webp"
                               class="form-control @error('screenshot') is-invalid @enderror" required>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload me-1"></i> Upload
                        </button>
                    </div>
                    @error('screenshot')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                    <div class="form-text">
                        JPEG, PNG or WebP. Images are re-encoded to WebP and stripped of metadata on upload.
                        @unless($canModerate) Your upload is reviewed by a manager before it appears in the gallery. @endunless
                    </div>
                </form>
            </div>
        @endif
        </div>{{-- /tab-content --}}
    </div>
</div>

@once
<style>
    :root {
        --gl-ease: cubic-bezier(0.16, 1, 0.3, 1);
        /* The lightbox is always dark, whatever the page theme. */
        --gl-surface: #0d1117;
        --gl-backdrop: rgba(10, 12, 16, .92);
        --gl-text: #e9ecef;
        --gl-text-muted: #adb5bd;
    }
    /* Thumbnail grid — sizes to the container, not the viewport, so tiles stay
       a sensible size whether the gallery is full-width (edit) or in a column
       (detail) instead of ballooning into 3 giant shots on wide screens. */
    .aircraft-gallery .gallery-group {
        display: grid;
        /* Capped max (not 1fr) so a lone screenshot on the full-width edit page
           stays tile-sized instead of stretching across the whole row. */
        grid-template-columns: repeat(auto-fill, minmax(200px, 260px));
        gap: 1rem;
        justify-content: start;
    }

    /* Thumbnails */
    .aircraft-gallery .gallery-thumb {
        display: block;
        width: 100%;
        padding: 0;
        border: 0;
        border-radius: .5rem;
        overflow: hidden;
        cursor: zoom-in;
        position: relative;
        background: var(--gl-surface);
        box-shadow: 0 .125rem .25rem rgba(0,0,0,.075);
    }
    .aircraft-gallery .gallery-thumb.is-pending { opacity: .85; }
    .aircraft-gallery .gallery-thumb img {
        display: block;
        width: 100%;
        aspect-ratio: 16 / 9;
        object-fit: cover;
        transition: transform .25s var(--gl-ease);
    }
    .aircraft-gallery .gallery-thumb:hover img,
    .aircraft-gallery .gallery-thumb:focus-visible img {
        transform: scale(1.03);
    }
    .aircraft-gallery .gallery-thumb-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.5rem;
        opacity: 0;
        background: rgba(0,0,0,.25);
        transition: opacity .15s ease-out;
        text-shadow: 0 1px 4px rgba(0,0,0,.6);
        pointer-events: none;
    }
    .aircraft-gallery .gallery-thumb:hover .gallery-thumb-overlay,
    .aircraft-gallery .gallery-thumb:focus-visible .gallery-thumb-overlay {
        opacity: 1;
    }

    /* Lightbox: a native modal <dialog>, so the browser traps focus, makes the
       page behind it inert and closes it on Esc. It fills the viewport; the
       dimmed backdrop is its ::backdrop. */
    #gallery-lightbox {
        width: 100vw;
        height: 100vh;
        max-width: 100vw;
        max-height: 100vh;
        margin: 0;
        padding: 0;
        border: 0;
        background: transparent;
        align-items: center;
        justify-content: center;
    }
    #gallery-lightbox[open] { display: flex; }
    #gallery-lightbox::backdrop {
        background: var(--gl-backdrop);
        backdrop-filter: blur(4px);
        animation: gl-fade .2s ease-out;
    }
    #gallery-lightbox[open] figure { animation: gl-enter .3s var(--gl-ease); }
    /* Exit is quicker than entry (set by close() before the dialog closes). */
    #gallery-lightbox.is-closing,
    #gallery-lightbox.is-closing::backdrop { animation: gl-fade .12s ease-in reverse forwards; }
    #gallery-lightbox figure {
        margin: 0;
        max-width: 92vw;
        max-height: 92vh;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    #gallery-lightbox img {
        max-width: 92vw;
        max-height: 82vh;
        object-fit: contain;
        border-radius: .375rem;
        box-shadow: 0 1rem 3rem rgba(0,0,0,.5);
    }
    @keyframes gl-fade {
        from { opacity: 0; }
        to   { opacity: 1; }
    }
    @keyframes gl-enter {
        from { transform: scale(.96); opacity: 0; }
        to   { transform: scale(1);   opacity: 1; }
    }
    #gallery-lightbox figcaption {
        color: var(--gl-text);
        margin-top: .75rem;
        font-size: .9rem;
        text-align: center;
    }
    #gallery-lightbox figcaption .gl-counter {
        color: var(--gl-text-muted);
        margin-left: .5rem;
        font-variant-numeric: tabular-nums;
    }
    .gl-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        border: 0;
        width: 3rem;
        height: 3rem;
        border-radius: 50%;
        background: rgba(255,255,255,.1);
        color: #fff;
        font-size: 1.4rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background .2s ease;
    }
    .gl-btn:hover { background: rgba(255,255,255,.25); }
    .gl-btn:focus-visible {
        outline: 2px solid #fff;
        outline-offset: 2px;
        background: rgba(255,255,255,.25);
    }
    .gl-prev { left: 1.25rem; }
    .gl-next { right: 1.25rem; }
    .gl-close {
        position: absolute;
        top: 1.25rem;
        right: 1.25rem;
        transform: none;
    }
    .gl-btn.d-none { display: none; }
    @media (max-width: 576px) {
        .gl-prev { left: .4rem; }
        .gl-next { right: .4rem; }
    }
    /* Touch screens: tile actions reach the 44px target size. */
    @media (pointer: coarse) {
        .aircraft-gallery .gallery-tile .btn-sm {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .25rem;
        }
    }
    /* Reduced motion: no scaling anywhere, only short fades, so opening,
       stepping and closing still read as state changes. */
    @media (prefers-reduced-motion: reduce) {
        .aircraft-gallery .gallery-thumb img { transition: none; }
        .aircraft-gallery .gallery-thumb:hover img,
        .aircraft-gallery .gallery-thumb:focus-visible img { transform: none; }
        #gallery-lightbox[open] figure { animation: gl-fade .15s ease-out; }
    }
</style>

<script>
(function () {
    // Build the lightbox chrome once and reuse it for every gallery group.
    const lb = document.createElement('dialog');
    lb.id = 'gallery-lightbox';
    lb.setAttribute('aria-labelledby', 'gallery-lightbox-caption');
    lb.innerHTML =
        '<button type="button" class="gl-btn gl-close" aria-label="Close">' +
            '<i class="bi bi-x-lg" aria-hidden="true"></i></button>' +
        '<button type="button" class="gl-btn gl-prev" aria-label="Previous screenshot">' +
            '<i class="bi bi-chevron-left" aria-hidden="true"></i></button>' +
        '<button type="button" class="gl-btn gl-next" aria-label="Next screenshot">' +
            '<i class="bi bi-chevron-right" aria-hidden="true"></i></button>' +
        '<figure>' +
            // src and alt are set by render() before the dialog opens.
            '<img alt="">' +
            '<figcaption id="gallery-lightbox-caption" aria-live="polite"><span class="gl-text"></span><span class="gl-counter"></span></figcaption>' +
        '</figure>';
    document.body.appendChild(lb);

    const img = lb.querySelector('img');
    const text = lb.querySelector('.gl-text');
    const counter = lb.querySelector('.gl-counter');
    const prevBtn = lb.querySelector('.gl-prev');
    const nextBtn = lb.querySelector('.gl-next');

    let items = [];   // [{ src, caption, alt }]
    let index = 0;
    let opener = null; // thumbnail to return focus to on close

    function render() {
        const item = items[index];
        if (!item) return;
        img.src = item.src;
        img.alt = item.alt || item.caption;
        text.textContent = item.caption;
        counter.textContent = items.length > 1 ? (index + 1) + ' / ' + items.length : '';
        const solo = items.length < 2;
        prevBtn.classList.toggle('d-none', solo);
        nextBtn.classList.toggle('d-none', solo);
    }

    function open(list, start, from) {
        items = list;
        index = start;
        opener = from;
        render();
        lb.showModal();
        document.body.style.overflow = 'hidden';
    }

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    // Fade out briefly, then close. The fallback timer covers a missed
    // animationend (e.g. the tab is hidden mid-animation).
    function close() {
        if (!lb.open || lb.classList.contains('is-closing')) return;
        lb.classList.add('is-closing');
        let done = false;
        const finish = function () {
            if (done) return;
            done = true;
            lb.classList.remove('is-closing');
            lb.close();
        };
        lb.addEventListener('animationend', finish, { once: true });
        setTimeout(finish, 200);
    }

    // Esc: run the same fade instead of the instant native close.
    lb.addEventListener('cancel', function (e) {
        e.preventDefault();
        close();
    });

    // Runs for every way of closing (button, backdrop, Esc).
    lb.addEventListener('close', function () {
        document.body.style.overflow = '';
        img.removeAttribute('src');
        if (opener && opener.isConnected) opener.focus();
        opener = null;
    });

    function step(delta) {
        if (items.length < 2) return;
        index = (index + delta + items.length) % items.length;
        render();
        // Crossfade (shorter, no slide, with reduced motion).
        img.animate(
            reduceMotion.matches
                ? [{ opacity: .4 }, { opacity: 1 }]
                : [{ opacity: .3, transform: 'translateX(' + (delta * 12) + 'px)' }, { opacity: 1, transform: 'none' }],
            { duration: reduceMotion.matches ? 120 : 220, easing: 'cubic-bezier(0.16, 1, 0.3, 1)' }
        );
    }

    // Delegate clicks; each lightbox set is scoped to its own .gallery-group so
    // approved and pending shots don't mix in the prev/next navigation.
    document.addEventListener('click', function (e) {
        const thumb = e.target.closest('.gallery-thumb');
        if (!thumb) return;
        const group = thumb.closest('.gallery-group');
        const thumbs = Array.from(group.querySelectorAll('.gallery-thumb'));
        const list = thumbs.map(function (t) {
            const thumbImg = t.querySelector('img');
            return {
                src: t.dataset.galleryFull,
                caption: t.dataset.galleryCaption || '',
                alt: thumbImg ? thumbImg.alt : '',
            };
        });
        open(list, thumbs.indexOf(thumb), thumb);
    });

    prevBtn.addEventListener('click', function () { step(-1); });
    nextBtn.addEventListener('click', function () { step(1); });
    lb.querySelector('.gl-close').addEventListener('click', close);
    // Click on the dark backdrop (not the image/buttons) closes.
    lb.addEventListener('click', function (e) { if (e.target === lb) close(); });

    // Esc is handled natively by the dialog.
    lb.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowLeft') step(-1);
        else if (e.key === 'ArrowRight') step(1);
    });
})();
</script>
@endonce
