{{-- Thêm script modules cho theme shadcn --}}
@if(isset($shadcn_scripts))
    @foreach($shadcn_scripts as $name => $url)
        <script type="module" src="{{ $url }}" nonce="{{ $cspNonce }}"></script>
    @endforeach
@endif 