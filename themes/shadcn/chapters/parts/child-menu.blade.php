<div data-component="chapter-contents" class="chapter-child-menu">
    @php
        // Chuẩn bị dữ liệu chapter và pages
        $isChapter = $bookChild instanceof \BookStack\Entities\Models\Chapter;
        
        // Lấy sub-chapters và pages nếu chưa có
        $visible_sub_chapters = $bookChild->visible_sub_chapters ?? 
            ($isChapter ? \Themes\Shadcn\App\Entities\Models\ChapterRelations::getVisibleSubChapters($bookChild) : collect([]));
            
        $visible_pages = $bookChild->visible_pages ?? 
            ($isChapter ? $bookChild->getVisiblePages() : collect([]));
        
        // Đảm bảo không bị null
        $visible_sub_chapters = $visible_sub_chapters ?? collect([]);
        $visible_pages = $visible_pages ?? collect([]);
        
        // Kiểm tra xem có children không
        $hasSubChapters = $visible_sub_chapters->count() > 0;
        $hasPages = $visible_pages->count() > 0;
        $hasChildren = $hasSubChapters || $hasPages;
        
        // Tính toán mức độ thụt lề
        $nestingLevel = $nestingLevel ?? 0;
        $nextNestingLevel = $nestingLevel + 1;
        $indentValue = 'calc(' . $nestingLevel . ' * var(--indent-step) + var(--indent-base))';
        
        // Kiểm tra xem phần này có chứa item hiện tại không
        $containsCurrent = $isOpen;
        
        // Kiểm tra xem có chapter con hoặc page con nào được chọn không
        if (!$containsCurrent) {
            foreach ($visible_sub_chapters as $subChapter) {
                if ($subChapter->matchesOrContains($current)) {
                    $containsCurrent = true;
                    break;
                }
            }
        }
        
        if (!$containsCurrent) {
            foreach ($visible_pages as $page) {
                if ($current->matches($page)) {
                    $containsCurrent = true;
                    break;
                }
            }
        }
    @endphp
    
    @if($hasChildren)
    <div class="chapter-child-menu" style="--indent: {{ $indentValue }}">
        <ul data-list="chapter-contents"
            data-section-id="chapter-contents-{{ $bookChild->id }}"
            data-section-type="list"
            class="chapter-contents-list sub-menu inset-list @if($containsCurrent) open @endif"
            @if($containsCurrent) style="display: block;" @else style="display: none;" @endif
            role="menu">
            
            {{-- Sub-chapters --}}
            @foreach($visible_sub_chapters as $childChapter)
                @php
                    $childMatches = $current->matches($childChapter);
                    $childContainsCurrent = $childChapter->matchesOrContains($current);
                @endphp
                <li class="list-item-chapter @if($childMatches) selected-chapter @endif" role="presentation">
                    @include('entities.list-item-basic', [
                        'entity' => $childChapter, 
                        'classes' => $childMatches ? 'selected' : '',
                        'showIconInTitle' => true,
                        'hasChildren' => ($childChapter->visible_sub_chapters && $childChapter->visible_sub_chapters->count() > 0) || 
                                         ($childChapter->visible_pages && $childChapter->visible_pages->count() > 0),
                        'isOpen' => $childContainsCurrent
                    ])
                    
                    @php
                        $childHasSubChapters = $childChapter->visible_sub_chapters && $childChapter->visible_sub_chapters->count() > 0;
                        $childHasPages = $childChapter->visible_pages && $childChapter->visible_pages->count() > 0;
                        $childHasChildren = $childHasSubChapters || $childHasPages;
                    @endphp
                    
                    @if($childHasChildren)
                    {{-- Đệ quy hiển thị sub-chapters và pages --}}
                    @include('chapters.parts.child-menu', [
                        'bookChild' => $childChapter,
                        'current' => $current,
                        'isOpen' => $childContainsCurrent,
                        'nestingLevel' => $nextNestingLevel
                    ])
                    @endif
                </li>
            @endforeach

            {{-- Pages --}}
            @foreach($visible_pages as $childPage)
                <li class="list-item-page {{ $childPage->isA('page') && $childPage->draft ? 'draft' : '' }} @if($current->matches($childPage)) selected-page @endif" role="presentation">
                    @include('entities.list-item-basic', [
                        'entity' => $childPage, 
                        'classes' => $current->matches($childPage)? 'selected' : '',
                        'showIconInTitle' => true
                    ])
                </li>
            @endforeach
        </ul>
    </div>
    @endif
</div>