@extends('dashboard.layouts.master')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3" style="background-color: #6f42c1; color: white; padding: 15px; border-radius: 8px;">
                        <h6 class="text-white text-capitalize ps-3 px-3 m-0">إعدادات الدقائق المجانية للطلاب الجدد</h6>
                    </div>
                </div>
                
                <div class="card-body px-4 pb-2 mt-4">
                    @if(session('success'))
                        <div class="alert alert-success text-white" style="background-color: #198754; color: white;">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger text-white" style="background-color: #dc3545; color: white;">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('settings.updateFreeMinutes') }}" method="POST">
                        @csrf
                        
                        <div class="row mb-4 mt-3">
                            <div class="col-md-12">
                                <div class="form-check form-switch d-flex align-items-center mb-3">
                                    <input class="form-check-input" type="checkbox" id="free_minutes_enabled" name="free_minutes_enabled" value="1" {{ $setting->free_minutes_enabled ? 'checked' : '' }} style="width: 40px; height: 20px; cursor: pointer;">
                                    <label class="form-check-label mb-0 ms-3 fw-bold" for="free_minutes_enabled" style="margin-right: 15px; font-size: 16px; cursor: pointer;">
                                        تفعيل منح دقائق مجانية للطلاب الجدد عند التسجيل
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">عدد الدقائق المجانية</label>
                                    <input type="number" class="form-control" name="free_minutes_amount" value="{{ old('free_minutes_amount', $setting->free_minutes_amount ?? 0) }}" min="0" step="1" required style="border: 1px solid #d2d6da; padding: 10px; border-radius: 6px; width: 100%;">
                                    <small class="text-muted d-block mt-1">الرصيد الذي سيحصل عليه الطالب تلقائياً.</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-group">
                                    <label class="form-label font-weight-bold">صلاحية الدقائق (بالأيام)</label>
                                    <input type="number" class="form-control" name="free_minutes_validity_days" value="{{ old('free_minutes_validity_days', $setting->free_minutes_validity_days ?? 0) }}" min="0" step="1" required style="border: 1px solid #d2d6da; padding: 10px; border-radius: 6px; width: 100%;">
                                    <small class="text-muted d-block mt-1">أدخل 0 إذا كانت الدقائق بدون تاريخ انتهاء.</small>
                                </div>
                            </div>
                        </div>

                        <div class="text-start mt-4 mb-3">
                            <button type="submit" class="btn btn-primary" style="background-color: #6f42c1; border: none; padding: 10px 20px; border-radius: 6px;">
                                <i class="fas fa-save me-2"></i> حفظ الإعدادات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
