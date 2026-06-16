@extends('layouts.app')

@section('content')
    <div id="map-container" class="container-fluid p-0" data-mode="{{ $mode ?? 'rome' }}">
    </div>
@endsection