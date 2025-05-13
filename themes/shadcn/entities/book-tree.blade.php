<nav id="book-tree"
     class="book-tree mb-xl"
     aria-label="{{ trans('entities.books_navigation') }}">

    <h5>{{ trans('entities.books_navigation') }}</h5>

    <ul class="sidebar-page-list mt-xs menu entity-list">
        @if (userCan('view', $book))
            <li class="list-item-book book">
                @include('entities.list-item-basic', ['entity' => $book, 'classes' => ($current->matches($book)? 'selected' : '')])
            </li>
        @endif

        @if(isset($sidebarTree['chapters']))
            @foreach($sidebarTree['chapters'] as $bookChild)
                <li class="list-item-{{ $bookChild->getType() }} {{ $bookChild->getType() }} {{ $bookChild->isA('page') && $bookChild->draft ? 'draft' : '' }}">
                    @include('entities.list-item-basic', ['entity' => $bookChild, 'classes' => $current->matches($bookChild)? 'selected' : ''])

                    @if($bookChild->isA('chapter'))
                        {{-- Hiển thị các trang trong chapter --}}
                        @if(count($bookChild->visible_pages) > 0)
                            <div class="entity-list-item no-hover">
                                <span role="presentation" class="icon text-chapter"></span>
                                <div class="content">
                                    @include('chapters.parts.child-menu', [
                                        'chapter' => $bookChild,
                                        'current' => $current,
                                        'isOpen'  => $bookChild->matchesOrContains($current)
                                    ])
                                </div>
                            </div>
                        @endif
                        
                        {{-- Hiển thị các chapter con --}}
                        @if(isset($bookChild->child_chapters) && count($bookChild->child_chapters) > 0)
                            <div class="sub-chapter-list ml-xl">
                                <ul class="sub-chapter-list-items">
                                    @foreach($bookChild->child_chapters as $childChapter)
                                        <li class="list-item-chapter chapter">
                                            @include('entities.list-item-basic', ['entity' => $childChapter, 'classes' => $current->matches($childChapter)? 'selected' : ''])
                                            
                                            {{-- Hiển thị các trang trong chapter con --}}
                                            @if(isset($childChapter->visible_pages) && count($childChapter->visible_pages) > 0)
                                                <div class="entity-list-item no-hover">
                                                    <span role="presentation" class="icon text-chapter"></span>
                                                    <div class="content">
                                                        @include('chapters.parts.child-menu', [
                                                            'chapter' => $childChapter,
                                                            'current' => $current,
                                                            'isOpen'  => $childChapter->matchesOrContains($current)
                                                        ])
                                                    </div>
                                                </div>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    @endif
                </li>
            @endforeach
        @elseif(is_a($sidebarTree, 'Illuminate\Support\Collection'))
            @foreach($sidebarTree as $bookChild)
                <li class="list-item-{{ $bookChild->getType() }} {{ $bookChild->getType() }} {{ $bookChild->isA('page') && $bookChild->draft ? 'draft' : '' }}">
                    @include('entities.list-item-basic', ['entity' => $bookChild, 'classes' => $current->matches($bookChild)? 'selected' : ''])

                    @if($bookChild->isA('chapter'))
                        {{-- Hiển thị các trang trong chapter --}}
                        @if(count($bookChild->visible_pages) > 0)
                            <div class="entity-list-item no-hover">
                                <span role="presentation" class="icon text-chapter"></span>
                                <div class="content">
                                    @include('chapters.parts.child-menu', [
                                        'chapter' => $bookChild,
                                        'current' => $current,
                                        'isOpen'  => $bookChild->matchesOrContains($current)
                                    ])
                                </div>
                            </div>
                        @endif
                        
                        {{-- Hiển thị các chapter con --}}
                        @if(isset($bookChild->child_chapters) && count($bookChild->child_chapters) > 0)
                            <div class="sub-chapter-list ml-xl">
                                <ul class="sub-chapter-list-items">
                                    @foreach($bookChild->child_chapters as $childChapter)
                                        <li class="list-item-chapter chapter">
                                            @include('entities.list-item-basic', ['entity' => $childChapter, 'classes' => $current->matches($childChapter)? 'selected' : ''])
                                            
                                            {{-- Hiển thị các trang trong chapter con --}}
                                            @if(isset($childChapter->visible_pages) && count($childChapter->visible_pages) > 0)
                                                <div class="entity-list-item no-hover">
                                                    <span role="presentation" class="icon text-chapter"></span>
                                                    <div class="content">
                                                        @include('chapters.parts.child-menu', [
                                                            'chapter' => $childChapter,
                                                            'current' => $current,
                                                            'isOpen'  => $childChapter->matchesOrContains($current)
                                                        ])
                                                    </div>
                                                </div>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    @endif
                </li>
            @endforeach
        @endif
        
        {{-- Hiển thị các trang trực tiếp thuộc book --}}
        @if(isset($sidebarTree['pages']))
            @foreach($sidebarTree['pages'] as $page)
                <li class="list-item-page page {{ $page->draft ? 'draft' : '' }}">
                    @include('entities.list-item-basic', ['entity' => $page, 'classes' => $current->matches($page)? 'selected' : ''])
                </li>
            @endforeach
        @endif
    </ul>
</nav>