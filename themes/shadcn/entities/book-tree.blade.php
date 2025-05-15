<nav id="book-tree"
     class="book-tree mb-xl"
     aria-label="{{ trans('entities.books_navigation') }}">

    <h5>{{ trans('entities.books_navigation') }}</h5>

    <ul class="sidebar-page-list mt-xs menu entity-list">
        @if (userCan('view', $book))
            <li class="list-item-book book">
                @include('entities.list-item-basic', ['entity' => $book, 'classes' => ($current->matches($book)? 'selected' : ''), 'showIconInTitle' => true])
            </li>
        @endif

        @foreach($sidebarTree as $bookChild)
            {{-- Chỉ hiển thị chapter gốc (không có parent) hoặc page không thuộc chapter nào --}}
            @if (!$bookChild->isA('chapter') || !isset($bookChild->parent_id) || empty($bookChild->parent_id))
                @php
                    $hasChildren = false;
                    $isOpen = false;
                    $matchesCurrent = false;
                    
                    if($bookChild->isA('chapter')) {
                        // Tính hasChildren
                        $visible_sub_chapters = $bookChild->visible_sub_chapters ?? 
                            \Themes\Shadcn\App\Entities\Models\ChapterRelations::getVisibleSubChapters($bookChild);
                        $visible_pages = $bookChild->visible_pages ?? $bookChild->getVisiblePages();
                        
                        // Đảm bảo không bị null
                        $visible_sub_chapters = $visible_sub_chapters ?? collect([]);
                        $visible_pages = $visible_pages ?? collect([]);
                        
                        $hasChildren = $visible_sub_chapters->count() > 0 || $visible_pages->count() > 0;
                        $matchesCurrent = $current->matches($bookChild);
                        $isOpen = $bookChild->matchesOrContains($current);
                        
                        // Kiểm tra thêm: bất kỳ sub-chapter, sub-sub-chapter, hoặc page nào được chọn đều mở ra
                        if (!$isOpen) {
                            // Kiểm tra các chapter con
                            foreach ($visible_sub_chapters as $child) {
                                if ($child->matchesOrContains($current)) {
                                    $isOpen = true;
                                    break;
                                }
                            }
                            
                            // Kiểm tra các page con
                            if (!$isOpen) {
                                foreach ($visible_pages as $page) {
                                    if ($current->matches($page)) {
                                        $isOpen = true;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    
                    // Root level indentation
                    $rootIndentValue = 'var(--indent-base)';
                @endphp
                
                <li class="list-item-{{ $bookChild->getType() }} {{ $bookChild->getType() }} {{ $bookChild->isA('page') && $bookChild->draft ? 'draft' : '' }} @if($matchesCurrent) selected-item @endif">
                    @include('entities.list-item-basic', [
                        'entity' => $bookChild, 
                        'classes' => $matchesCurrent ? 'selected' : '', 
                        'showIconInTitle' => true,
                        'hasChildren' => $bookChild->isA('chapter') ? $hasChildren : false,
                        'isOpen' => $bookChild->isA('chapter') ? $isOpen : false,
                        'isRoot' => true
                    ])

                    @if($bookChild->isA('chapter') && $hasChildren)
                        <div class="chapter-child-menu" style="--indent: {{ $rootIndentValue }}">
                            <ul data-list="chapter-contents"
                                data-section-id="chapter-contents-{{ $bookChild->id }}"
                                data-section-type="list"
                                class="chapter-contents-list sub-menu inset-list @if($isOpen) open @endif"
                                @if($isOpen) style="display: block;" @else style="display: none;" @endif
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
                                        
                                        @if(($childChapter->visible_sub_chapters && $childChapter->visible_sub_chapters->count() > 0) || 
                                            ($childChapter->visible_pages && $childChapter->visible_pages->count() > 0))
                                            {{-- Direct include without extra wrappers --}}
                                            @include('chapters.parts.child-menu', [
                                                'bookChild' => $childChapter,
                                                'current' => $current,
                                                'isOpen' => $childContainsCurrent,
                                                'nestingLevel' => 1
                                            ])
                                        @endif
                                    </li>
                                @endforeach

                                {{-- Pages --}}
                                @foreach($visible_pages as $childPage)
                                    <li class="list-item-page {{ $childPage->isA('page') && $childPage->draft ? 'draft' : '' }} @if($current->matches($childPage)) selected-page @endif" role="presentation">
                                        @include('entities.list-item-basic', [
                                            'entity' => $childPage, 
                                            'classes' => $current->matches($childPage) ? 'selected' : '',
                                            'showIconInTitle' => true
                                        ])
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </li>
            @endif
        @endforeach
    </ul>
</nav>