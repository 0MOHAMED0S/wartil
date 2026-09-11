@extends('dashboard.layouts.master')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-5">
        <div class="col-lg-8 col-md-10 mx-auto">
            <div class="card mt-4 shadow-sm border-0">
                <div class="card-header p-3 bg-gradient-primary text-white border-radius-xl mt-n4 mx-4 shadow-primary text-center">
                    <h5 class="mb-0 text-white font-weight-bolder">
                        <i class="fas fa-gift me-2"></i> إعدادات الدقائق المجانية للطلاب الجدد
                    </h5>
                    <p class="text-sm mb-0 opacity-8">تحكم في الهدايا التي يحصل عليها الطلاب الجدد عند تسجيل حساباتهم.</p>
                </div>
                
                <div class="card-body p-4 mt-2">
                    @if(session('success'))
                        <div class="alert alert-success text-white alert-dismissible fade show" role="alert">
                            <span class="alert-icon align-middle"><i class="fas fa-check-circle"></i></span>
                            <span class="alert-text fw-bold">{{ session('success') }}</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger text-white alert-dismissible fade show" role="alert">
                            <span class="alert-icon align-middle"><i class="fas fa-exclamation-triangle"></i></span>
                            <span class="alert-text">
                                <ul class="mb-0 ps-3">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <form action="{{ route('settings.updateFreeMinutes') }}" method="POST">
                        @csrf
                        
                        <!-- Toggle Switch -->
                        <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-light rounded-3 border">
                            <div>
                                <h6 class="mb-1 text-dark">تفعيل الميزة</h6>
                                <p class="text-sm text-muted mb-0">إذا تم تفعيل هذا الخيار، سيحصل الطلاب الجدد على دقائق مجانية تلقائياً.</p>
                            </div>
                            <div class="form-check form-switch ps-0">
                                <input class="form-check-input ms-auto float-end" type="checkbox" id="free_minutes_enabled" name="free_minutes_enabled" value="1" {{ $setting->free_minutes_enabled ? 'checked' : '' }} style="transform: scale(1.3); cursor: pointer;">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold text-dark">عدد الدقائق المجانية <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-outline {{ $setting->free_minutes_amount ? 'is-filled' : '' }}">
                                        <input type="number" class="form-control px-3 border" name="free_minutes_amount" value="{{ old('free_minutes_amount', $setting->free_minutes_amount ?? 0) }}" min="0" step="1" required placeholder="مثال: 30">
                                    </div>
                                    <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle ms-1"></i> الرصيد الذي سيتم إضافته للمحفظة.</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold text-dark">فترة الصلاحية (بالأيام) <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-outline {{ $setting->free_minutes_validity_days ? 'is-filled' : '' }}">
                                        <input type="number" class="form-control px-3 border" name="free_minutes_validity_days" value="{{ old('free_minutes_validity_days', $setting->free_minutes_validity_days ?? 0) }}" min="0" step="1" required placeholder="مثال: 7">
                                    </div>
                                    <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle ms-1"></i> اكتب 0 لدقائق دائمة بدون انتهاء.</small>
                                </div>
                            </div>
                        </div>

                        <hr class="horizontal dark my-4">

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn bg-gradient-primary btn-lg mb-0">
                                <i class="fas fa-save me-2"></i> حفظ التغييرات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
