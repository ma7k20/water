@extends('layouts.app')

@section('content')
<h4 class="mb-3">إضافة مشترك جديد</h4>
<div class="card p-3">
    <form method="POST" action="{{ route('customers.store') }}">
        @include('customers._form')
    </form>
</div>
@endsection
