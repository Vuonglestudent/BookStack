@component('entities.list-item-basic', ['entity' => $page, 'showIconInTitle' => true])
    <div class="entity-item-snippet">
        <p class="text-muted break-text">{{ $page->getExcerpt() }}</p>
    </div>
@endcomponent