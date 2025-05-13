@push('head')
    <script src="{{ versioned_asset('libs/tinymce/tinymce.min.js') }}" nonce="{{ $cspNonce }}"></script>
@endpush

{{ csrf_field() }}

{{-- Store key variables in PHP to avoid repeated checks --}}
@php
    // Safe entities with fallbacks
    $safeBook = $book ?? null;
    $safeChapter = $chapter ?? null;
    $safeParentChapter = $parentChapter ?? null;
    
    // Safe slugs with fallbacks
    $bookSlug = isset($bookSlug) ? $bookSlug : (isset($safeBook) && isset($safeBook->slug) ? $safeBook->slug : '');
    $chapterSlug = isset($chapterSlug) ? $chapterSlug : (isset($safeChapter) && isset($safeChapter->slug) ? $safeChapter->slug : '');
@endphp

<div class="form-group title-input">
    <label for="name">{{ trans('common.name') }}</label>
    @include('form.text', ['name' => 'name', 'autofocus' => true])
</div>

{{-- Thêm trường chọn chapter cha --}}
<div class="form-group">
    <label for="parent_id">{{ trans('entities.chapter_parent') }}</label>
    <select name="parent_id" id="parent_id" class="standard-select">
        <option value="">{{ trans('entities.chapters_no_parent') }}</option>
        @if(isset($availableParentChapters) && count($availableParentChapters) > 0)
            @foreach($availableParentChapters as $parentOption)
                @php
                    $isSelected = false;
                    // Check if this is the parent chapter (from URL)
                    if (isset($safeParentChapter) && isset($safeParentChapter->id) && $parentOption->id == $safeParentChapter->id) {
                        $isSelected = true;
                    }
                    // Check if chapter has this parent set
                    if (!$isSelected && isset($safeChapter) && isset($safeChapter->parent_id) && $safeChapter->parent_id == $parentOption->id) {
                        $isSelected = true;
                    }
                    // Check if it's in the old input
                    if (!$isSelected && old('parent_id') == $parentOption->id) {
                        $isSelected = true;
                    }
                @endphp
                <option value="{{ $parentOption->id }}" {{ $isSelected ? 'selected' : '' }}>
                    {{ $parentOption->name }}
                </option>
            @endforeach
        @endif
    </select>
    
    @if(isset($debug))
    <!-- Debug info:
        Parent Chapter ID: {{ $debug['parentChapterId'] ?? 'none' }}
        Chapter Parent ID: {{ $debug['chapterParentId'] ?? 'none' }}
    -->
    @endif
</div>

<div class="form-group description-input">
    <label for="description_html">{{ trans('common.description') }}</label>
    @include('form.description-html-input')
</div>

<div class="form-group collapsible" component="collapsible" id="logo-control">
    <button refs="collapsible@trigger" type="button" class="collapse-title text-link" aria-expanded="false">
        <label for="tags">{{ trans('entities.chapter_tags') }}</label>
    </button>
    <div refs="collapsible@content" class="collapse-content">
        @include('entities.tag-manager', ['entity' => $safeChapter])
    </div>
</div>

<div class="form-group collapsible" component="collapsible" id="template-control">
    <button refs="collapsible@trigger" type="button" class="collapse-title text-link" aria-expanded="false">
        <label for="template-manager">{{ trans('entities.default_template') }}</label>
    </button>
    <div refs="collapsible@content" class="collapse-content">
        @include('entities.template-selector', ['entity' => $safeChapter])
    </div>
</div>

<div class="form-group text-right">
    @php
        $cancelUrl = '/';
        if (isset($safeChapter) && method_exists($safeChapter, 'getUrl')) {
            try {
                $cancelUrl = $safeChapter->getUrl();
            } catch (\Exception $e) {
                // Fallback if getUrl fails
            }
        } elseif (isset($safeParentChapter) && method_exists($safeParentChapter, 'getUrl')) {
            try {
                $cancelUrl = $safeParentChapter->getUrl();
            } catch (\Exception $e) {
                // Fallback if getUrl fails
            }
        } elseif (isset($safeBook) && method_exists($safeBook, 'getUrl')) {
            try {
                $cancelUrl = $safeBook->getUrl();
            } catch (\Exception $e) {
                // Fallback if getUrl fails
            }
        }
    @endphp
    <a href="{{ $cancelUrl }}" class="button outline">{{ trans('common.cancel') }}</a>
    <button type="submit" class="button">{{ trans('entities.chapters_save') }}</button>
</div>

@include('entities.selector-popup')
@include('form.editor-translations')