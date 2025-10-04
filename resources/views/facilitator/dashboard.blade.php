@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="alert alert-info">
        <h3>Peer Facilitator Dashboard</h3>
        <p>Welcome, {{ auth()->user()->name }}!</p>
        <p>You have limited access to assist in student support.</p>
    </div>
</div>
@endsection
