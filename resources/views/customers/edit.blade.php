@extends('layouts.app')

@section('content')
<h4 class="mb-3">تعديل بيانات مشترك</h4>
<div class="card p-3">
    <form method="POST" action="{{ route('customers.update', $customer) }}">
        @method('PUT')
        @include('customers._form')
    </form>
</div>
@endsection
