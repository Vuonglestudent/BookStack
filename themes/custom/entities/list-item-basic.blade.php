<?php $type = $entity->getType(); ?>
<a href="{{ $entity->getUrl() }}" class="{{$type}} {{$type === 'page' && $entity->draft ? 'draft' : ''}} {{$classes ?? ''}} entity-list-item" data-entity-type="{{$type}}" data-entity-id="{{$entity->id}}">
    <div class="content">
        <h4 class="entity-list-item-name break-text">
            <span role="presentation" class="icon text-{{$type}} mr-xs" style="display: inline-block; vertical-align: middle;">@icon($type)</span>
            {{ $entity->preview_name ?? $entity->name }}
        </h4>
        {{ $slot ?? '' }}
    </div>
</a>
