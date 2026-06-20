@extends('layouts.admin')

@section('page-title', 'Add Product')

@section('content')
    <div class="">
        <div class="mb-5">
            <a href="{{ route('admin.products.index') }}" class="text-sm text-slate-500 hover:text-slate-700">&larr; Back to
                Products</a>
        </div>

        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" id="product-form">
            @include('admin.products._form')

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('admin.products.index') }}"
                    class="rounded-lg border border-slate-300 text-sm font-medium px-5 py-2.5 text-slate-600 hover:bg-slate-50">Cancel</a>
                <button type="submit"
                    class="rounded-lg bg-indigo-600 hover:bg-indigo-500 text-sm font-medium px-5 py-2.5 text-white shadow-sm">Create
                    Product</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    @include('admin.products._form-scripts')
@endpush
