@extends('layouts.app')

@section('title', 'Store Billing — New Order')

@section('content')
    <!-- Global Reusable Blade Alert Component for API Warnings, Errors & Notifications -->
    <x-alert id="alertBanner" dismissible="true" />

    <!-- Main 12-Column Responsive Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- Left Column (Span 8): Customer Info, Product Selection Table, Payment Computation -->
        <div class="lg:col-span-8 space-y-6">
            @include('partials.customer-section')
            @include('partials.products-section')
            @include('partials.payment-section')
        </div>

        <!-- Right Column (Span 4): Low Stock Alert & Complete Dispatch Card -->
        <div class="lg:col-span-4 space-y-6">
            @include('partials.low-stock-alert')
            @include('partials.dispatch-section')
        </div>

    </div>

    <!-- On-Screen Generated Bill Section -->
    @include('partials.generated-bill')
@endsection
