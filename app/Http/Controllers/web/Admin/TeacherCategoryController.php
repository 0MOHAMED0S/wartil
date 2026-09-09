<?php
namespace App\Http\Controllers\web\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherCategory;
use Illuminate\Http\Request;

class TeacherCategoryController extends Controller
{
    public function index()
    {
        $categories = TeacherCategory::all();
        return view('dashboard.teacher_categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'egypt_rate' => 'required|numeric|min:0',
            'arab_rate' => 'required|numeric|min:0',
            'foreign_rate' => 'required|numeric|min:0',
        ]);
        TeacherCategory::create($request->only('name', 'egypt_rate', 'arab_rate', 'foreign_rate'));
        return redirect()->back()->with('success', 'تم إضافة الفئة بنجاح');
    }

    public function update(Request $request, $id)
    {
        $category = TeacherCategory::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'egypt_rate' => 'required|numeric|min:0',
            'arab_rate' => 'required|numeric|min:0',
            'foreign_rate' => 'required|numeric|min:0',
        ]);
        $category->update($request->only('name', 'egypt_rate', 'arab_rate', 'foreign_rate'));
        return redirect()->back()->with('success', 'تم تحديث الفئة بنجاح');
    }

    public function destroy($id)
    {
        $category = TeacherCategory::findOrFail($id);
        if ($category->teachers()->count() > 0) {
            return redirect()->back()->with('error', 'لا يمكن حذف الفئة لوجود معلمين مرتبطين بها.');
        }
        $category->delete();
        return redirect()->back()->with('success', 'تم حذف الفئة بنجاح');
    }
}
