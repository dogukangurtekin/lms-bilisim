@extends('layout.app')
@section('title', 'Flamestone Skorlar')
@section('content')
<div class="top"><h1>Flamestone Leaderboard</h1></div>
<x-leaderboard :scores="$scores" />
@endsection
