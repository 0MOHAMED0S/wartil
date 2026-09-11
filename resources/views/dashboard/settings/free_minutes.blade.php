@extends('dashboard.layouts.master')

@section('content')
<!-- Import SweetAlert2 for modern alerts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .gift-settings-card {
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        border: none;
        overflow: hidden;
    }
    .gift-header {
        background: linear-gradient(135deg, #6f42c1 0%, #8965cd 100%);
        color: white;
        padding: 25px;
        border-radius: 15px 15px 0 0;
    }
    .gift-icon-container {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        margin-bottom: 15px;
    }
    .gift-icon-container i {
        font-size: 28px;
        color: white;
    }
    .settings-wrapper {
        padding: 30px;
        background: #ffffff;
    }
    .switch-container {
        background: #f8f9fa;
        border: 1px solid #edf2f9;
        border-radius: 12px;
        padding: 20px;
        transition: all 0.3s ease;
    }
    .switch-container:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.03);
    }
    .form-control-custom {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 12px 15px;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        width: 100%;
        background-color: #fff;
    }
    .form-control-custom:focus {
        border-color: #6f42c1;
        box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.25);
        outline: 0;
    }
    .btn-save {
        background: linear-gradient(135deg, #6f42c1 0%, #8965cd 100%);
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 8px;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: all 0.3s;
    }
    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(111, 66, 193, 0.3);
        color: white;
    }
</style>

<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            
            <div class="card gift-settings-card">
                <!-- Header -->
                <div class="gift-header text-center">
                    <div class="gift-icon-container">
                        <i class="fas fa-gift"></i>
                    </div>
                    <h3 class="text-white mb-2 fw-bold">إعدادات الدقائق المجانية</h3>
                    <p class="text-white-50 mb-0">تحكم في الهدايا الترحيبية للطلاب الجدد عند التسجيل في المنصة</p>
                </div>
                
                <!-- Body -->
                <div class="settings-wrapper">
                    
                    <!-- Alerts -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert" style="background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; border-radius: 8px;">
                            <i class="fas fa-check-circle me-2"></i> <strong>نجاح!</strong> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; border-radius: 8px;">
                            <i class="fas fa-exclamation-triangle me-2"></i> <strong>تنبيه!</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('settings.updateFreeMinutes') }}" method="POST">
                        @csrf
                        
                        <!-- Toggle Switch -->
                        <div class="switch-container mb-4 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1 fw-bold" style="color: #2b3445;">تفعيل الميزة</h5>
                                <p class="text-muted mb-0 font-size-sm">عند تفعيل هذا الخيار، سيحصل الطلاب الجدد على الرصيد المجاني بمجرد إكمال التسجيل.</p>
                            </div>
                            <div class="form-check form-switch ps-0 m-0">
                                <input class="form-check-input float-end" type="checkbox" id="free_minutes_enabled" name="free_minutes_enabled" value="1" {{ $setting->free_minutes_enabled ? 'checked' : '' }} style="width: 50px; height: 25px; cursor: pointer;">
                            </div>
                        </div>

                        <!-- Inputs Grid -->
                        <div class="row g-4 mb-4">
                            <!-- Minutes Amount -->
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="form-label fw-bold" style="color: #495057;">عدد الدقائق المجانية <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control-custom" name="free_minutes_amount" value="{{ old('free_minutes_amount', $setting->free_minutes_amount ?? 0) }}" min="0" step="1" required placeholder="مثال: 30">
                                    <small class="text-muted mt-2 d-block"><i class="fas fa-info-circle ms-1 text-primary"></i> الرصيد الذي سيتم إضافته لمحفظة الطالب.</small>
                                </div>
                            </div>
                            
                            <!-- Validity Days -->
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="form-label fw-bold" style="color: #495057;">فترة الصلاحية (بالأيام) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control-custom" name="free_minutes_validity_days" value="{{ old('free_minutes_validity_days', $setting->free_minutes_validity_days ?? 0) }}" min="0" step="1" required placeholder="مثال: 7">
                                    <small class="text-muted mt-2 d-block"><i class="fas fa-info-circle ms-1 text-primary"></i> اكتب <strong class="text-dark">0</strong> لتكون الدقائق بدون تاريخ انتهاء.</small>
                                </div>
                            </div>
                        </div>

                        <hr style="background-color: #e9ecef; height: 2px; margin: 30px 0;">

                        <!-- Submit Button -->
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn-save">
                                <i class="fas fa-save me-2"></i> حفظ الإعدادات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleSwitch = document.getElementById('free_minutes_enabled');
        
        toggleSwitch.addEventListener('change', function() {
            if(this.checked) {
                Swal.fire({
                    title: 'تم التفعيل!',
                    text: 'سيتم الآن منح الدقائق المجانية للطلاب الجدد، لا تنسَ حفظ الإعدادات.',
                    icon: 'success',
                    confirmButtonText: 'حسناً',
                    confirmButtonColor: '#198754'
                });
            } else {
                Swal.fire({
                    title: 'تم التعطيل!',
                    text: 'لن يحصل الطلاب الجدد على دقائق مجانية بعد الآن، لا تنسَ حفظ الإعدادات.',
                    icon: 'warning',
                    confirmButtonText: 'حسناً',
                    confirmButtonColor: '#fd7e14'
                });
            }
        });
    });
</script>
@endsection
