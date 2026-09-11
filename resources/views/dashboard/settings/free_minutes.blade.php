@extends('dashboard.layouts.master')

@section('title', 'إعدادات الهدايا')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow-sm border-0 rounded-4 my-4" style="background-color: #fff;">
                <div class="card-header border-bottom-0 pb-0 pt-4 px-4 bg-white rounded-top-4">
                    <div class="d-flex align-items-center">
                        <div class="icon-box rounded-circle d-flex align-items-center justify-content-center me-3 ms-3" style="width: 55px; height: 55px; background-color: var(--primary-light); color: var(--primary-dark);">
                            <i class="fa-solid fa-gift fs-3" style="color: var(--primary-dark);"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold" style="color: var(--primary-dark); font-size: 1.4rem;">إعدادات الدقائق المجانية للطلاب الجدد</h5>
                            <p class="text-muted small mb-0 mt-1" style="font-size: 0.95rem;">تحكم في الدقائق المهداة للطلاب عند تسجيل حساب جديد في المنصة</p>
                        </div>
                    </div>
                    <hr class="mt-4 mb-0" style="opacity: 0.1;">
                </div>
                
                <div class="card-body px-5 py-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show text-white shadow-sm border-0" role="alert" style="background-color: var(--primary-dark); border-radius: 10px;">
                            <div class="d-flex align-items-center">
                                <i class="fa-solid fa-circle-check fs-4 me-2 ms-2"></i>
                                <div>
                                    <strong>تم بنجاح!</strong> {{ session('success') }}
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="filter: invert(1); padding: 1.25rem 1rem;"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show text-white shadow-sm border-0" role="alert" style="border-radius: 10px;">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fa-solid fa-triangle-exclamation fs-4 me-2 ms-2"></i>
                                <strong>تنبيه! يرجى مراجعة الأخطاء التالية:</strong>
                            </div>
                            <ul class="mb-0 mt-1 ps-4 pe-4">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="filter: invert(1); padding: 1.25rem 1rem;"></button>
                        </div>
                    @endif

                    <form action="{{ route('settings.updateFreeMinutes') }}" method="POST">
                        @csrf
                        
                        <div class="p-4 rounded-4 mb-5" style="background-color: #f8f9fa; border: 1px solid #e9ecef;">
                            <div class="form-check form-switch d-flex align-items-center mb-0 p-0">
                                <input class="form-check-input mt-0 shadow-sm" type="checkbox" id="free_minutes_enabled" name="free_minutes_enabled" value="1" {{ $setting->free_minutes_enabled ? 'checked' : '' }} style="width: 55px; height: 28px; cursor: pointer; float: right; margin-left: 15px; border: none; background-color: {{ $setting->free_minutes_enabled ? 'var(--primary-dark)' : '#cbd5e1' }};">
                                <label class="form-check-label mb-0 fw-bold text-dark" for="free_minutes_enabled" style="cursor: pointer; margin-right: 60px;">
                                    <span style="font-size: 1.2rem; color: #2d3436;">تفعيل نظام الهدايا للطلاب</span>
                                    <span class="d-block text-muted fw-normal mt-1" style="font-size: 0.9rem;">عند تفعيل هذا الخيار، سيحصل أي طالب جديد يسجل في المنصة على رصيد مجاني من الدقائق تلقائياً.</span>
                                </label>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 mb-4">
                                <div class="form-group position-relative">
                                    <label class="form-label fw-bold text-dark mb-2" style="font-size: 1.05rem;">
                                        <i class="fa-regular fa-clock text-muted ms-1"></i> رصيد الهدايا (بالدقائق)
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control form-control-lg bg-light" name="free_minutes_amount" value="{{ old('free_minutes_amount', $setting->free_minutes_amount ?? 0) }}" min="0" step="1" required style="border: 1px solid #dee2e6; border-radius: 0 8px 8px 0; font-size: 1.2rem; padding: 12px 15px; font-weight: bold; color: var(--primary-dark);">
                                        <span class="input-group-text bg-light text-muted" style="border: 1px solid #dee2e6; border-right: none; border-radius: 8px 0 0 8px;">دقيقة</span>
                                    </div>
                                    <div class="form-text text-muted mt-2" style="font-size: 0.85rem;"><i class="fa-solid fa-circle-info ms-1"></i>الرصيد الذي سيضاف تلقائياً للطالب.</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <div class="form-group position-relative">
                                    <label class="form-label fw-bold text-dark mb-2" style="font-size: 1.05rem;">
                                        <i class="fa-regular fa-calendar-check text-muted ms-1"></i> صلاحية الدقائق (بالأيام)
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control form-control-lg bg-light" name="free_minutes_validity_days" value="{{ old('free_minutes_validity_days', $setting->free_minutes_validity_days ?? 0) }}" min="0" step="1" required style="border: 1px solid #dee2e6; border-radius: 0 8px 8px 0; font-size: 1.2rem; padding: 12px 15px; font-weight: bold; color: var(--primary-dark);">
                                        <span class="input-group-text bg-light text-muted" style="border: 1px solid #dee2e6; border-right: none; border-radius: 8px 0 0 8px;">يوم</span>
                                    </div>
                                    <div class="form-text text-muted mt-2" style="font-size: 0.85rem;"><i class="fa-solid fa-circle-info ms-1"></i>أدخل <strong class="text-danger">0</strong> لتكون الدقائق صالحة مدى الحياة.</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4 pt-4" style="border-top: 1px solid #f1f3f5;">
                            <button type="submit" class="btn btn-lg text-white px-5 rounded-pill shadow-sm d-flex align-items-center" style="background-color: var(--primary-dark); transition: all 0.3s ease; font-size: 1.1rem;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 15px rgba(45,138,116,0.3)';" onmouseout="this.style.transform='none'; this.style.boxShadow='none';">
                                <i class="fa-solid fa-floppy-disk ms-2 fs-5"></i> حفظ التغييرات
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    // تغيير لون خلفية زر الـ Toggle ديناميكياً
    document.addEventListener('DOMContentLoaded', function() {
        const toggleSwitch = document.getElementById('free_minutes_enabled');
        
        toggleSwitch.addEventListener('change', function() {
            if(this.checked) {
                this.style.backgroundColor = 'var(--primary-dark)';
                this.style.borderColor = 'var(--primary-dark)';
            } else {
                this.style.backgroundColor = '#cbd5e1';
                this.style.borderColor = '#cbd5e1';
            }
        });
    });
</script>
@endsection

