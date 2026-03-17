@extends('admin.layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h3 class="fw-bold">Welcome back, {{ auth()->user()->name }}!</h3>
        <p class="text-muted">Here's what's happening with your document platform today.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Stat Card 1 -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                        <i class="fas fa-users text-primary fs-4"></i>
                    </div>
                </div>
                <h6 class="text-muted mb-1 fw-medium text-uppercase small">Total Users</h6>
                <h2 class="fw-bold mb-0"><?= $users ?></h2>
            </div>
        </div>
    </div>

    <!-- Stat Card 2 -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="bg-info bg-opacity-10 p-3 rounded-circle">
                        <i class="fas fa-file-alt text-info fs-4"></i>
                    </div>
                </div>
                <h6 class="text-muted mb-1 fw-medium text-uppercase small">Documents</h6>
                <h2 class="fw-bold mb-0"><?= $documents ?></h2>
            </div>
        </div>
    </div>

    <!-- Stat Card 3 -->
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                        <i class="fas fa-database text-warning fs-4"></i>
                    </div>
                </div>
                <h6 class="text-muted mb-1 fw-medium text-uppercase small">Storage Used</h6>
                <h2 class="fw-bold mb-0"><?= $totalSizeMb.'MB'?></h2>
            </div>
        </div>
    </div>

</div>

@endsection
