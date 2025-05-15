{{-- This file should not be loaded directly, but only included as a Blade component --}}
@if(isset($entity))
    <?php 
    $type = $entity->getType(); 
    $debugId = $entity->id ?? 'unknown';
    ?>
    <a href="{{ $entity->getUrl() }}" class="{{$type}} {{$type === 'page' && $entity->draft ? 'draft' : ''}} {{$classes ?? ''}} entity-list-item" data-entity-type="{{$type}}" data-entity-id="{{$entity->id}}">
        <span role="presentation" class="icon text-{{$type}}">
            @icon($type)    
        </span>
        <div class="content">
            <h4 class="entity-list-item-name break-text">
                <span class="entity-name-text">
                    @if(isset($showIconInTitle) && $showIconInTitle)
                        @icon($type)
                    @endif
                    {{ $entity->preview_name ?? $entity->name }}
                </span>
                @if($type === 'chapter' && (isset($hasChildren) && $hasChildren))
                    <button type="button"
                        data-toggle="chapter-contents"
                        data-section-id="chapter-contents-{{ $entity->id }}"
                        data-section-type="toggle"
                        data-debug-id="{{ $debugId }}"
                        data-context="{{ isset($isRoot) ? 'root' : 'child' }}"
                        aria-expanded="{{ isset($isOpen) && $isOpen ? 'true' : 'false' }}"
                        class="text-muted chapter-toggle-btn @if(isset($isOpen) && $isOpen) open @endif"
                        onclick="event.preventDefault(); event.stopPropagation();">
                        @icon('caret-right')
                    </button>
                @endif
            </h4>
            {{ $slot ?? '' }}
        </div>
    </a>
@else
    <!-- $entity variable is not available when the template is loaded directly -->
    <p>This is a BookStack Blade template and should not be accessed directly.</p>
@endif
