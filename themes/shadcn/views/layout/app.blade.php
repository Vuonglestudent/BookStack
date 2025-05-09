{{-- themes/shadcn/views/layout/app.blade.php --}}
@extends('layouts.app')

@section('head')
    @parent
    <link rel="stylesheet" href="{{ url('/themes/shadcn/css/index.css') }}">
@endsection 