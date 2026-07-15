{{--
    Aircraft screenshot gallery.
    Expects:
      $aircraft  - the Aircraft model (with `images` loaded)
      $canManage - bool, whether the current user may upload/delete/set-primary

    Thumbnails open in a self-contained lightbox overlay (see the @once block
    at the bottom) with keyboard + prev/next navigation - no new tab, no
    external dependencies.
--}}
@php($images = $aircraft->images)
<div class="card border-0 shadow-sm mb-4 aircraft-gallery">
    <div class="card-header bg-white py-3 fw-bold border-bottom d-flex align-items-center">
        <i class="bi bi-images text-muted me-2"></i> Aircraft Gallery
        <span class="badge bg-secondary ms-2 fs-7">{{ $images->count() }}</span>
    </div>
    <div class="card-body">
        @if($images->isEmpty())
            <div class="text-center text-muted py-4">
                <i class="bi bi-image fs-1 text-secondary opacity-50 d-block mb-2"></i>
                No screenshots yet.
                @if($canManage) Upload the first one below. @endif
            </div>
        @else
            <div class="row g-3">
                @foreach($images as $img)
                    <div class="col-6 col-md-4">
                        <div class="position-relative gallery-tile">
                            <button type="button"
                                    class="gallery-thumb"
                                    data-gallery-full="{{ route('aircraft.images.show', [$aircraft->id, $img->id]) }}"
                                    data-gallery-caption="{{ $aircraft->registration }}{{ $img->is_primary ? ' · Main livery shot' : '' }}"
                                    aria-label="Open screenshot of {{ $aircraft->registration }}">
                                <img src="{{ route('aircraft.images.show', [$aircraft->id, $img->id]) }}"
                                     alt="Screenshot of {{ $aircraft->registration }}"
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
                            @if($canManage)
                                <div class="d-flex gap-1 mt-2">
                                    @unless($img->is_primary)
                                        <form action="{{ route('aircraft.images.primary', [$aircraft->id, $img->id]) }}" method="post" class="flex-grow-1">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-primary w-100" title="Set as main">
                                                <i class="bi bi-star"></i> Main
                                            </button>
                                        </form>
                                    @endunless
                                    <form action="{{ route('aircraft.images.destroy', [$aircraft->id, $img->id]) }}" method="post"
                                          onsubmit="return confirm('Delete this screenshot?');" class="{{ $img->is_primary ? 'flex-grow-1' : '' }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger w-100" title="Delete">
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

        @if($canManage)
            <form action="{{ route('aircraft.images.store', $aircraft->id) }}" method="post" enctype="multipart/form-data"
                  class="mt-4 pt-3 border-top">
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
                </div>
            </form>
        @endif
    </div>
</div>

@once
<style>
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
        background: #0d1117;
        box-shadow: 0 .125rem .25rem rgba(0,0,0,.075);
    }
    .aircraft-gallery .gallery-thumb img {
        display: block;
        width: 100%;
        aspect-ratio: 16 / 9;
        object-fit: cover;
        transition: transform .35s ease, opacity .2s ease;
    }
    .aircraft-gallery .gallery-thumb:hover img,
    .aircraft-gallery .gallery-thumb:focus-visible img {
        transform: scale(1.06);
        opacity: .85;
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
        transition: opacity .25s ease;
        text-shadow: 0 1px 4px rgba(0,0,0,.6);
        pointer-events: none;
    }
    .aircraft-gallery .gallery-thumb:hover .gallery-thumb-overlay,
    .aircraft-gallery .gallery-thumb:focus-visible .gallery-thumb-overlay {
        opacity: 1;
    }

    /* Lightbox overlay */
    #gallery-lightbox {
        position: fixed;
        inset: 0;
        z-index: 2000;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(10, 12, 16, .92);
        backdrop-filter: blur(4px);
        opacity: 0;
        transition: opacity .2s ease;
    }
    #gallery-lightbox.is-open { display: flex; opacity: 1; }
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
        animation: gallery-zoom .2s ease;
    }
    @keyframes gallery-zoom {
        from { transform: scale(.96); opacity: 0; }
        to   { transform: scale(1);   opacity: 1; }
    }
    #gallery-lightbox figcaption {
        color: #e9ecef;
        margin-top: .75rem;
        font-size: .9rem;
        text-align: center;
    }
    #gallery-lightbox figcaption .gl-counter {
        color: #adb5bd;
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
</style>

<script>
(function () {
    // Build the lightbox chrome once and reuse it for every gallery on the page.
    const lb = document.createElement('div');
    lb.id = 'gallery-lightbox';
    lb.innerHTML =
        '<button type="button" class="gl-btn gl-close" aria-label="Close">' +
            '<i class="bi bi-x-lg"></i></button>' +
        '<button type="button" class="gl-btn gl-prev" aria-label="Previous">' +
            '<i class="bi bi-chevron-left"></i></button>' +
        '<button type="button" class="gl-btn gl-next" aria-label="Next">' +
            '<i class="bi bi-chevron-right"></i></button>' +
        '<figure>' +
            '<img alt="">' +
            '<figcaption><span class="gl-text"></span><span class="gl-counter"></span></figcaption>' +
        '</figure>';
    document.body.appendChild(lb);

    const img = lb.querySelector('img');
    const text = lb.querySelector('.gl-text');
    const counter = lb.querySelector('.gl-counter');
    const prevBtn = lb.querySelector('.gl-prev');
    const nextBtn = lb.querySelector('.gl-next');

    let items = [];   // [{ src, caption }]
    let index = 0;

    function render() {
        const item = items[index];
        if (!item) return;
        img.src = item.src;
        img.alt = item.caption;
        text.textContent = item.caption;
        counter.textContent = items.length > 1 ? (index + 1) + ' / ' + items.length : '';
        const solo = items.length < 2;
        prevBtn.classList.toggle('d-none', solo);
        nextBtn.classList.toggle('d-none', solo);
    }

    function open(list, start) {
        items = list;
        index = start;
        render();
        lb.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function close() {
        lb.classList.remove('is-open');
        document.body.style.overflow = '';
        img.src = '';
    }

    function step(delta) {
        if (!items.length) return;
        index = (index + delta + items.length) % items.length;
        render();
    }

    // Delegate clicks so it also covers galleries added after load.
    document.addEventListener('click', function (e) {
        const thumb = e.target.closest('.gallery-thumb');
        if (!thumb) return;
        const gallery = thumb.closest('.aircraft-gallery');
        const thumbs = Array.from(gallery.querySelectorAll('.gallery-thumb'));
        const list = thumbs.map(function (t) {
            return { src: t.dataset.galleryFull, caption: t.dataset.galleryCaption || '' };
        });
        open(list, thumbs.indexOf(thumb));
    });

    prevBtn.addEventListener('click', function () { step(-1); });
    nextBtn.addEventListener('click', function () { step(1); });
    lb.querySelector('.gl-close').addEventListener('click', close);
    // Click on the dark backdrop (not the image/buttons) closes.
    lb.addEventListener('click', function (e) { if (e.target === lb) close(); });

    document.addEventListener('keydown', function (e) {
        if (!lb.classList.contains('is-open')) return;
        if (e.key === 'Escape') close();
        else if (e.key === 'ArrowLeft') step(-1);
        else if (e.key === 'ArrowRight') step(1);
    });
})();
</script>
@endonce
