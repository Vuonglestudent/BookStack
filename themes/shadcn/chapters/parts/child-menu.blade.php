<div data-component="chapter-contents" class="chapter-child-menu">
    @php
        // Chuẩn bị dữ liệu chapter và pages
        $isChapter = $bookChild instanceof \BookStack\Entities\Models\Chapter;
        
        // Lấy sub-chapters và pages nếu chưa có
        $bookChild->visible_sub_chapters = $bookChild->visible_sub_chapters ?? 
            ($isChapter ? \Themes\Shadcn\App\Entities\Models\ChapterRelations::getVisibleSubChapters($bookChild) : collect([]));
            
        $bookChild->visible_pages = $bookChild->visible_pages ?? 
            ($isChapter ? $bookChild->getVisiblePages() : collect([]));
        
        // ID cho component
        $chaptersId = 'chapter-list-' . $bookChild->id;
        $pagesId = 'page-list-' . $bookChild->id;
    @endphp
    
    {{-- Sub-chapters section if exists --}}
    @if($bookChild->visible_sub_chapters->count() > 0)
        <div class="mb-s chapter-section">
            <button type="button"
                    data-toggle="chapter-contents"
                    data-section-id="{{ $chaptersId }}"
                    data-section-type="toggle"
                    aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
                    class="text-muted chapter-contents-toggle @if($isOpen) open @endif">
                @icon('caret-right') @icon('chapter') <span>{{ trans_choice('entities.x_chapters', $bookChild->visible_sub_chapters->count()) }}</span>
            </button>
            <ul data-list="chapter-contents"
                data-section-id="{{ $chaptersId }}"
                data-section-type="list"
                class="chapter-contents-list sub-menu inset-list @if($isOpen) open @endif" @if($isOpen)
                style="display: block;" @endif
                role="menu">
                @foreach($bookChild->visible_sub_chapters as $childChapter)
                    <li class="list-item-chapter" role="presentation">
                        @include('entities.list-item-basic', ['entity' => $childChapter, 'classes' => $current->matches($childChapter)? 'selected' : '' ])
                        
                        <div class="entity-list-item no-hover" style="padding-left: 12px;">
                            <span role="presentation" class="icon text-chapter"></span>
                            <div class="content">
                                {{-- Đệ quy hiển thị sub-chapters và pages --}}
                                @include('chapters.parts.child-menu', [
                                    'bookChild' => $childChapter,
                                    'current' => $current,
                                    'isOpen'  => $childChapter->matchesOrContains($current)
                                ])
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Pages section if exists --}}
    @if($bookChild->visible_pages->count() > 0)
        <div class="mb-s chapter-section">
            <button type="button"
                    data-toggle="chapter-contents"
                    data-section-id="{{ $pagesId }}"
                    data-section-type="toggle"
                    aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
                    class="text-muted chapter-contents-toggle @if($isOpen) open @endif">
                @icon('caret-right') @icon('page') <span>{{ trans_choice('entities.x_pages', $bookChild->visible_pages->count()) }}</span>
            </button>
            <ul data-list="chapter-contents"
                data-section-id="{{ $pagesId }}"
                data-section-type="list"
                class="chapter-contents-list sub-menu inset-list @if($isOpen) open @endif" @if($isOpen)
                style="display: block;" @endif
                role="menu">
                @foreach($bookChild->visible_pages as $childPage)
                    <li class="list-item-page {{ $childPage->isA('page') && $childPage->draft ? 'draft' : '' }}" role="presentation">
                        @include('entities.list-item-basic', ['entity' => $childPage, 'classes' => $current->matches($childPage)? 'selected' : '' ])
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>