@extends('layouts.tri')

@section('body')
    <div class="mb-m print-hidden">
        @include('entities.breadcrumbs', ['crumbs' => [
            $book,
            $chapter,
        ]])
    </div>

    <main class="content-wrap card">
        <h1 class="break-text">{{ $chapter->name }}</h1>
        <div class="chapter-content" dir="auto">
            {!! $chapter->descriptionHtml() !!}
        </div>

        @php
            // Get visible sub-chapters using the helper function
            $subChapters = \Themes\Shadcn\App\Entities\Models\ChapterRelations::getVisibleSubChapters($chapter);
            
            // Make sure each subchapter has its visible_pages and visible_sub_chapters initialized
            foreach($subChapters as $subChapter) {
                if (!isset($subChapter->visible_pages)) {
                    $subChapter->visible_pages = $subChapter->getVisiblePages();
                }
                
                if (!isset($subChapter->visible_sub_chapters)) {
                    $subChapter->visible_sub_chapters = \Themes\Shadcn\App\Entities\Models\ChapterRelations::getVisibleSubChapters($subChapter);
                }
            }
            
            $hasChildren = $subChapters->count() > 0 || count($pages) > 0;
        @endphp

        @if($hasChildren)
            <div class="entity-list book-contents">
                {{-- Display sub-chapters --}}
                @foreach($subChapters as $childElement)
                    @include('chapters.parts.list-item', ['chapter' => $childElement, 'isShowChildren' => false])
                @endforeach

                {{-- Display pages --}}
                @foreach($pages as $childElement)
                    @include('pages.parts.list-item', ['page' => $childElement])
                @endforeach
            </div>
        @else
            <div class="mt-xl">
                <hr>
                <p class="text-muted italic mt-xl mb-m">{{ trans('entities.chapters_empty') }}</p>

                <div class="icon-list block inline">
                    @if(userCan('page-create', $chapter))
                        <a href="{{ $chapter->getUrl('/create-page') }}" class="icon-list-item text-page">
                            <span class="icon">@icon('page')</span>
                            <span>{{ trans('entities.books_empty_create_page') }}</span>
                        </a>
                    @endif
                    @if(userCan('book-update', $book))
                        <a href="{{ $book->getUrl('/sort') }}" class="icon-list-item text-book">
                            <span class="icon">@icon('book')</span>
                            <span>{{ trans('entities.books_empty_sort_current_book') }}</span>
                        </a>
                    @endif
                </div>
            </div>
        @endif

        @include('entities.search-results')
    </main>

    @include('entities.sibling-navigation', ['next' => $next, 'previous' => $previous])

@stop

@section('right')

    <div class="mb-xl">
        <h5>{{ trans('common.details') }}</h5>
        <div class="blended-links">
            @include('entities.meta', ['entity' => $chapter, 'watchOptions' => $watchOptions])

            @if($book->tags->count() > 0)
                <div>
                    @include('entities.tag-list', ['entity' => $book, 'expandOnHover' => true])
                </div>
            @endif

            @if($chapter->tags->count() > 0)
                <div>
                    @include('entities.tag-list', ['entity' => $chapter, 'expandOnHover' => true])
                </div>
            @endif

        </div>
    </div>

    <div class="actions mb-xl">
        <h5>{{ trans('common.actions') }}</h5>
        <div class="icon-list text-link">

            @if(userCan('page-create', $chapter))
                <a href="{{ $chapter->getUrl('/create-page') }}" data-shortcut="new" class="icon-list-item">
                    <span>@icon('add')</span>
                    <span>{{ trans('entities.pages_new') }}</span>
                </a>
            @endif

            {{-- Đổi lại nút tạo sub-chapter để sử dụng role permission --}}
            @if(userCan('chapter-create', $book))
                <a href="{{ route('chapters.sub.create', ['bookSlug' => $book->slug, 'chapterSlug' => $chapter->slug]) }}" class="icon-list-item">
                    <span>@icon('add')</span>
                    <span>{{ trans('entities.chapters_new_sub') }}</span>
                </a>
            @endif

            <hr class="primary-background"/>

            @if(userCan('chapter-update', $chapter))
                <a href="{{ $chapter->getUrl('/edit') }}" data-shortcut="edit" class="icon-list-item">
                    <span>@icon('edit')</span>
                    <span>{{ trans('common.edit') }}</span>
                </a>
            @endif

            @if(userCan('chapter-update', $chapter) && userCan('chapter-delete', $chapter))
                <a href="{{ $chapter->getUrl('/move') }}" class="icon-list-item">
                    <span>@icon('folder')</span>
                    <span>{{ trans('common.move') }}</span>
                </a>
            @endif

            @if(userCan('chapter-delete', $chapter))
                <a href="{{ $chapter->getUrl('/delete') }}" class="icon-list-item">
                    <span>@icon('delete')</span>
                    <span>{{ trans('common.delete') }}</span>
                </a>
            @endif

            @if(userCan('update', $chapter))
                <a href="{{ $chapter->getUrl('/permissions') }}" class="icon-list-item">
                    <span>@icon('lock')</span>
                    <span>{{ trans('entities.permissions') }}</span>
                </a>
            @endif

            @if($chapter->book && userCan('book-update', $chapter->book))
                <hr class="primary-background"/>
                <a href="{{ $chapter->book->getUrl('/sort') }}" data-shortcut="sort" class="icon-list-item">
                    <span>@icon('sort')</span>
                    <span>{{ trans('entities.chapter_sort_book') }}</span>
                </a>
            @endif

            <hr class="primary-background"/>

            @if($watchOptions->canWatch() && !$watchOptions->isWatching())
                @include('entities.watch-action', ['entity' => $chapter])
            @endif
            @if(!user()->isGuest())
                @include('entities.favourite-action', ['entity' => $chapter])
            @endif
            @if(userCan('content-export'))
                @include('entities.export-menu', ['entity' => $chapter])
            @endif
        </div>
    </div>
@stop

@section('left')

    @include('entities.search-form', ['label' => trans('entities.chapters_search_this')])

    @if($chapter->tags->count() > 0)
        <div class="mb-xl">
            @include('entities.tag-list', ['entity' => $chapter])
        </div>
    @endif

    @include('entities.book-tree', ['book' => $book, 'sidebarTree' => $sidebarTree])
@stop