<?php
namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;
use function Termwind\render;

class FaqController extends Controller
{
    public function index()
    {
        $items = Faq::orderBy('order', 'asc')->paginate(20);
        return view('back.faqs.index', compact('items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'answer'   => 'required|string',
            'published'   => 'required|boolean',
            'order'    => 'nullable|integer',
        ]);

        $faq=Faq::create($request->all());
        $html=view('back.faqs.partials.faq-template',compact('faq'))->render();
        return response()->json(['success' => 'سوال متداول با موفقیت ایجاد شد.','html'=>$html]);
    }

    public function edit(Faq $faq)
    {
        return response()->json($faq);
    }

    public function update(Request $request, Faq $faq)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'answer'   => 'required|string',
            'published'   => 'required|boolean',
            'order'    => 'nullable|integer',
        ]);

        $faq->update($request->all());
        return response()->json(['success' => 'سوال متداول با موفقیت ویرایش شد.','faq'=>$faq,]);
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();
        return response()->json(['success' => 'سوال متداول با موفقیت حذف شد.']);
    }

    public function multipleDestroy(Request $request)
    {
        $ids = $request->input('ids');
        if ($ids) {
            Faq::whereIn('id', $ids)->delete();
            return response()->json(['success' => 'سوالات انتخاب شده با موفقیت حذف شدند.']);
        }
        return response()->json(['error' => 'موردی انتخاب نشده است.'], 400);
    }
}
