@extends('dashboard.layouts.master')

@section('styles')
    <link rel="stylesheet" href="{{ asset('dashboard/css/tracks.css') }}">
    <style>
        /* General Page & Cards */
        .page-title-box { border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 25px; }
        .pricing-card { background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.03); overflow: hidden; }
        .pricing-card-header { padding: 20px 25px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; background: #fff; }
        .pricing-card-title { font-weight: 800; font-size: 1.1rem; color: #1e293b; display: flex; align-items: center; gap: 10px; margin: 0; }
        .pricing-card-icon { color: #10b981; font-size: 1.3rem; }
        
        /* Premium Table Grid */
        .premium-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .premium-table th { padding: 18px 25px; font-weight: 700; color: #475569; font-size: 0.95rem; text-align: center; border-bottom: 2px solid #f1f5f9; background: #fafafa; }
        .premium-table th:first-child { text-align: right; }
        
        .premium-table td { padding: 18px 25px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; text-align: center; transition: 0.2s; }
        .premium-table td:first-child { text-align: right; border-right: none; }
        .premium-table tr:last-child td { border-bottom: none; }
        .premium-table tr:hover td { background-color: #f8fafc; }
        
        /* Category Name Cell */
        .cat-name-cell { display: flex; align-items: center; justify-content: space-between; gap: 15px; }
        .cat-name-text { font-weight: 800; color: #1e293b; font-size: 1rem; }
        
        /* Price Cell */
        .price-cell-content { display: flex; align-items: center; justify-content: center; gap: 15px; }
        .price-info { display: flex; flex-direction: column; align-items: center; }
        .price-value { font-weight: 800; font-size: 1.1rem; color: #0f172a; }
        .price-currency { font-size: 0.75rem; color: #64748b; font-weight: 600; margin-top: 2px; }
        
        /* Action Buttons */
        .btn-edit-inline { width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; border: 1px solid #e2e8f0; background: #fff; color: #64748b; font-size: 0.85rem; transition: 0.2s; cursor: pointer; }
        .btn-edit-inline:hover { background: #f0fdf4; border-color: #bbf7d0; color: #10b981; transform: translateY(-2px); box-shadow: 0 4px 6px rgba(16, 185, 129, 0.1); }
        
        .btn-delete-inline { color: #ef4444; background: none; border: none; padding: 5px; opacity: 0.7; transition: 0.2s; }
        .btn-delete-inline:hover { opacity: 1; transform: scale(1.1); color: #dc2626; }
        
        /* Form Inputs */
        .custom-input { border: 1px solid #e2e8f0; border-radius: 10px !important; padding: 10px 15px; background-color: #f8fafc; font-weight: 600; }
        .custom-input:focus { background-color: #fff; border-color: #10b981; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1); }
        
        /* Highlight Row (Like in Mockup) */
        .highlight-row { background-color: #f0fdf4 !important; }
        .highlight-row td { border-bottom-color: #dcfce7; }
        .highlight-row .cat-name-text, .highlight-row .price-value { color: #166534; }
        
        /* Primary Button */
        .btn-premium { background: #10b981; color: #fff; border: none; font-weight: 700; padding: 10px 24px; border-radius: 10px; transition: 0.3s; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); }
        .btn-premium:hover { background: #059669; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(16, 185, 129, 0.3); color: #fff; }
    </style>
@endsection

@section('title')
    <div class="d-flex justify-content-between align-items-center w-100">
        <h5 class="m-0 fw-bold fs-5 text-dark">إدارة فئات المعلمين</h5>
        <button class="btn btn-premium fw-bold px-4 rounded-pill shadow-sm py-1" style="font-size:0.9rem;" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="fa-solid fa-plus me-1"></i> إضافة فئة
        </button>
    </div>
@endsection

@section('content')
<div class="container-fluid p-0">
    @if(session('success'))
        <div class="alert alert-success fw-bold border-0 shadow-sm rounded-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger fw-bold border-0 shadow-sm rounded-3">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-3">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="pricing-card">
        <div class="pricing-card-header">
            <h5 class="pricing-card-title">
                <i class="fa-solid fa-globe pricing-card-icon"></i> 
                تسعير الفئات حسب الدولة
            </h5>
        </div>
        
        <div class="table-responsive">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>الفئة</th>
                        <th>مصري</th>
                        <th>عربي</th>
                        <th>غير عربي</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $index => $category)
                        {{-- The mockup highlights the last active category or 'الفئة الخامسة' --}}
                        <tr class="{{ $loop->last && $loop->count > 1 ? 'highlight-row' : '' }}">
                            <td>
                                <div class="cat-name-cell">
                                    <div class="d-flex flex-column align-items-start">
                                        <span class="cat-name-text">{{ $category->name }}</span>
                                        <span class="badge bg-light text-secondary mt-1 border" style="font-size: 0.75rem;">
                                            <i class="fa-solid fa-users me-1"></i> {{ $category->teachers()->count() }} معلم
                                        </span>
                                    </div>
                                    <button type="button" class="btn-delete-inline" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $category->id }}" title="حذف الفئة">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                            
                            {{-- Egypt Rate --}}
                            <td>
                                <div class="price-cell-content">
                                    <div class="price-info">
                                        <span class="price-value">{{ rtrim(rtrim(number_format($category->egypt_rate, 2, '.', ''), '0'), '.') }}</span>
                                        <span class="price-currency">جنيه / ساعة</span>
                                    </div>
                                    <button type="button" class="btn-edit-inline" data-bs-toggle="modal" data-bs-target="#editModal{{ $category->id }}" title="تعديل">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                </div>
                            </td>
                            
                            {{-- Arab Rate --}}
                            <td>
                                <div class="price-cell-content">
                                    <div class="price-info">
                                        <span class="price-value">{{ rtrim(rtrim(number_format($category->arab_rate, 2, '.', ''), '0'), '.') }}</span>
                                        <span class="price-currency">جنيه / ساعة</span>
                                    </div>
                                    <button type="button" class="btn-edit-inline" data-bs-toggle="modal" data-bs-target="#editModal{{ $category->id }}" title="تعديل">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                </div>
                            </td>
                            
                            {{-- Foreign Rate --}}
                            <td>
                                <div class="price-cell-content">
                                    <div class="price-info">
                                        <span class="price-value">{{ rtrim(rtrim(number_format($category->foreign_rate, 2, '.', ''), '0'), '.') }}</span>
                                        <span class="price-currency">جنيه / ساعة</span>
                                    </div>
                                    <button type="button" class="btn-edit-inline" data-bs-toggle="modal" data-bs-target="#editModal{{ $category->id }}" title="تعديل">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal{{ $category->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                                    <div class="modal-header bg-light border-0">
                                        <h5 class="modal-title fw-bold">
                                            <i class="fa-solid fa-pen-to-square text-success me-2"></i> تعديل أسعار {{ $category->name }}
                                        </h5>
                                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('teacher-categories.update', $category->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body p-4">
                                            <div class="mb-4">
                                                <label class="form-label fw-bold text-muted small">اسم الفئة</label>
                                                <input type="text" name="name" class="form-control custom-input" value="{{ $category->name }}" required>
                                            </div>
                                            
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold text-muted small">سعر مصري</label>
                                                    <div class="input-group">
                                                        <input type="number" name="egypt_rate" class="form-control custom-input border-end-0" step="0.01" min="0" value="{{ rtrim(rtrim(number_format($category->egypt_rate, 2, '.', ''), '0'), '.') }}" required>
                                                        <span class="input-group-text bg-light text-muted border-start-0" style="border-radius: 10px 0 0 10px; border-color: #e2e8f0; font-size:0.8rem;">ج.م</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold text-muted small">سعر عربي</label>
                                                    <div class="input-group">
                                                        <input type="number" name="arab_rate" class="form-control custom-input border-end-0" step="0.01" min="0" value="{{ rtrim(rtrim(number_format($category->arab_rate, 2, '.', ''), '0'), '.') }}" required>
                                                        <span class="input-group-text bg-light text-muted border-start-0" style="border-radius: 10px 0 0 10px; border-color: #e2e8f0; font-size:0.8rem;">ج.م</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold text-muted small">سعر غير عربي</label>
                                                    <div class="input-group">
                                                        <input type="number" name="foreign_rate" class="form-control custom-input border-end-0" step="0.01" min="0" value="{{ rtrim(rtrim(number_format($category->foreign_rate, 2, '.', ''), '0'), '.') }}" required>
                                                        <span class="input-group-text bg-light text-muted border-start-0" style="border-radius: 10px 0 0 10px; border-color: #e2e8f0; font-size:0.8rem;">ج.م</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0 pt-0">
                                            <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">إلغاء</button>
                                            <button type="submit" class="btn btn-premium rounded-pill px-4">حفظ التغييرات</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Delete Modal -->
                        <div class="modal fade" id="deleteModal{{ $category->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                                    <div class="modal-body p-4 text-center">
                                        <div class="mb-3">
                                            <i class="fa-solid fa-triangle-exclamation text-danger" style="font-size: 3rem;"></i>
                                        </div>
                                        <h5 class="fw-bold mb-3">حذف الفئة</h5>
                                        <p class="text-muted mb-4">هل أنت متأكد من رغبتك في حذف <strong>{{ $category->name }}</strong>؟ لا يمكن التراجع عن هذا الإجراء.</p>
                                        <form action="{{ route('teacher-categories.destroy', $category->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <div class="d-flex justify-content-center gap-2">
                                                <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">إلغاء</button>
                                                <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">حذف الفئة</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-folder-open fs-1 opacity-25 mb-3"></i>
                                <h6 class="fw-bold">لا يوجد فئات مضافة</h6>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header bg-light border-0">
                <h5 class="modal-title fw-bold">
                    <i class="fa-solid fa-layer-group text-success me-2"></i> إضافة فئة جديدة
                </h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('teacher-categories.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">اسم الفئة</label>
                        <input type="text" name="name" class="form-control custom-input" placeholder="مثال: الفئة الأولى" required>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-muted small">سعر مصري</label>
                            <div class="input-group">
                                <input type="number" name="egypt_rate" class="form-control custom-input border-end-0" step="0.01" min="0" placeholder="0.00" required>
                                <span class="input-group-text bg-light text-muted border-start-0" style="border-radius: 10px 0 0 10px; border-color: #e2e8f0; font-size:0.8rem;">ج.م</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-muted small">سعر عربي</label>
                            <div class="input-group">
                                <input type="number" name="arab_rate" class="form-control custom-input border-end-0" step="0.01" min="0" placeholder="0.00" required>
                                <span class="input-group-text bg-light text-muted border-start-0" style="border-radius: 10px 0 0 10px; border-color: #e2e8f0; font-size:0.8rem;">ج.م</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-muted small">سعر غير عربي</label>
                            <div class="input-group">
                                <input type="number" name="foreign_rate" class="form-control custom-input border-end-0" step="0.01" min="0" placeholder="0.00" required>
                                <span class="input-group-text bg-light text-muted border-start-0" style="border-radius: 10px 0 0 10px; border-color: #e2e8f0; font-size:0.8rem;">ج.م</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-premium rounded-pill px-4">إضافة الفئة</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
