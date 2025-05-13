@extends('layouts.simple')

@section('body')
    <div class="container small">

        <div class="my-s">
            @if(isset($parentChapter))
                @include('entities.breadcrumbs', ['crumbs' => [
                    $book,
                    $parentChapter,
                    $parentChapter->getUrl('/create-chapter') => [
                        'text' => trans('entities.chapters_create'),
                        'icon' => 'add',
                    ]
                ]])
            @else
                @include('entities.breadcrumbs', ['crumbs' => [
                    $book,
                    $book->getUrl('create-chapter') => [
                        'text' => trans('entities.chapters_create'),
                        'icon' => 'add',
                    ]
                ]])
            @endif
        </div>

        <main class="content-wrap card">
            <h1 class="list-heading">{{ trans('entities.chapters_create') }}</h1>
            @if(isset($parentChapter))
                <form action="{{ $parentChapter->getUrl('/create-chapter') }}" method="POST">
                    @include('chapters.parts.form', [
                        'book' => $book,
                        'chapter' => $chapter ?? null,
                        'parentChapter' => $parentChapter,
                        'availableParentChapters' => $availableParentChapters ?? []
                    ])
                </form>
            @else
                <form action="{{ $book->getUrl('/create-chapter') }}" method="POST">
                    @include('chapters.parts.form', [
                        'book' => $book,
                        'chapter' => $chapter ?? null,
                        'parentChapter' => null,
                        'availableParentChapters' => $availableParentChapters ?? []
                    ])
                </form>
            @endif
        </main>

    </div>
@stop 