{{--
    Aircraft screenshot gallery.
    Expects:
      $aircraft  - the Aircraft model (with `images` loaded)
      $canManage - bool, whether the current user may upload/delete/set-primary
--}}
@php($images = $aircraft->images)
<div class="card border-0 shadow-sm mb-4">
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
                        <div class="position-relative">
                            <a href="{{ route('aircraft.images.show', [$aircraft->id, $img->id]) }}" target="_blank" rel="noopener">
                                <img src="{{ route('aircraft.images.show', [$aircraft->id, $img->id]) }}"
                                     alt="Screenshot of {{ $aircraft->registration }}"
                                     class="img-fluid rounded shadow-sm w-100"
                                     style="aspect-ratio: 16/9; object-fit: cover;">
                            </a>
                            @if($img->is_primary)
                                <span class="badge bg-primary position-absolute top-0 start-0 m-2">
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
