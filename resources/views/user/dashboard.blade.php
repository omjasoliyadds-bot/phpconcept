@extends('user.layouts.user')

@section('content')

<div class="container-fluid">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start mb-3">
        <h4 class="fw-bold mb-0">Dashboard Overview</h4>
        <span class="text-muted">Welcome back, {{ auth()->user()->name }}</span>
    </div>

    <div class="row g-4">

        <!-- Storage Usage -->
        <div class="col-xl-4 col-md-6">
            <div class="card dashboard-card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box bg-info text-white">
                        <i class="fa fa-hdd"></i>
                    </div>
                    @php
                        $used = auth()->user()->used_storage;
                        $limit = auth()->user()->storage_limit;
                        $percentage = $limit > 0 ? min(($used / $limit) * 100, 100) : 0;
                    @endphp
                    <div class="ms-3 flex-grow-1">
                        <h6 class="text-muted mb-1">Storage Usage</h6>
                        <h6 class="fw-bold mb-1">{{ formatBytes($used) }} / {{ formatBytes($limit) }}</h6>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar {{ $percentage > 90 ? 'bg-danger' : ($percentage > 70 ? 'bg-warning' : 'bg-primary') }}" role="progressbar" style="width: {{ $percentage }}%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Documents -->
        <div class="col-xl-4 col-md-6">
            <div class="card dashboard-card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box bg-primary text-white">
                        <i class="fa fa-file-alt"></i>
                    </div>
                    <div class="ms-3">
                        <h6 class="text-muted mb-1">Total Documents</h6>
                        <h3 class="fw-bold mb-0">{{ $documentTotal }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Folders -->
        <div class="col-xl-4 col-md-6">
            <div class="card dashboard-card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box bg-success text-white">
                        <i class="fa fa-folder"></i>
                    </div>
                    <div class="ms-3">
                        <h6 class="text-muted mb-1">Total Folders</h6>
                        <h3 class="fw-bold mb-0">{{ $totalFolder }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Uploads Today -->
        <div class="col-xl-4 col-md-6">
            <div class="card dashboard-card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-box bg-warning text-white">
                        <i class="fa fa-cloud-upload-alt"></i>
                    </div>
                    <div class="ms-3">
                        <h6 class="text-muted mb-1">Uploads Today</h6>
                        <h3 class="fw-bold mb-0">{{ $uploadToday }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection