{{-- This file should not be loaded directly, but only included as a Blade component --}}
@if(isset($entity))
    <?php $type = $entity->getType(); ?>
    <a href="{{ $entity->getUrl() }}" class="{{$type}} {{$type === 'page' && $entity->draft ? 'draft' : ''}} {{$classes ?? ''}} entity-list-item" data-entity-type="{{$type}}" data-entity-id="{{$entity->id}}">
        <span role="presentation" class="icon text-{{$type}}">
            @icon($type)    
        </span>
        <div class="content">
            <h4 class="entity-list-item-name break-text">
                <span class="entity-name-text">{{ $entity->preview_name ?? $entity->name }}</span>
            </h4>
            {{ $slot ?? '' }}
        </div>
    </a>
@else
    <!-- $entity variable is not available when the template is loaded directly -->
    <p>This is a BookStack Blade template and should not be accessed directly.</p>
@endif
