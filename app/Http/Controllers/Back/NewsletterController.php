<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Newsletter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\NewslettersExport;

class NewsletterController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Newsletter::class, 'newsletters');
    }
    public function index(Request $request)
    {
        $subscribers = Newsletter::query()
            ->when($request->search, function($query) use ($request) {
                $query->where('contact', 'like', "%{$request->search}%");
            })
            ->when($request->contact_type, function($query) use ($request) {
                if ($request->contact_type == 'email') {
                    $query->where('contact', 'regexp', '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$');
                } elseif ($request->contact_type == 'mobile') {
                    $query->where('contact', 'regexp', '^09[0-9]{9}$');
                }
            })
            ->when($request->status, function($query) use ($request) {
                $query->where('is_active', $request->status == 'active');
            })
            ->when($request->device_type, function($query) use ($request) {
                $query->where('device_type', $request->device_type);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $statistics = [
            'total' => Newsletter::count(),
            'active' => Newsletter::where('is_active', true)->count(),
            'inactive' => Newsletter::where('is_active', false)->count(),
            'email' => Newsletter::emailSubscribers()->count(),
            'mobile' => Newsletter::mobileSubscribers()->count(),
        ];

        return view('back.newsletters.index', compact('subscribers', 'statistics'));
    }

    public function show(Newsletter $newsletter)
    {
        return view('back.newsletters.show', compact('newsletter'))->render();
    }

    public function destroy(Newsletter $newsletter)
    {

        $newsletter->delete();

        return response('success');
    }

    public function multipleDestroy(Request $request)
    {
        $this->authorize('newsletters.delete');

        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:newsletters,id',
        ]);

        foreach ($request->ids as $id) {
            $product = Newsletter::find($id);
            $this->destroy($product);
        }

        return response('success');
    }


    public function export(Request $request)
    {
        $type = $request->type;
        $format = $request->input('format') ?: 'xlsx';

        $query = Newsletter::query();

        switch ($type) {
            case 'email':
                $query->emailSubscribers();
                break;
            case 'mobile':
                $query->mobileSubscribers();
                break;
            case 'active':
                $query->where('is_active', true);
                break;
            case 'inactive':
                $query->where('is_active', false);
                break;
            case 'all':
            default:
                // همه موارد
                break;
        }

        $subscribers = $query->get();

        if ($format == 'csv') {
            return $this->exportCsv($subscribers, $type);
        }

        return Excel::download(new NewslettersExport($subscribers, $type), 'newsletters_' . $type . '_' . now()->format('Y-m-d') . '.xlsx');
    }

    private function exportCsv($subscribers, $type)
    {
        $filename = 'newsletters_' . $type . '_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($subscribers) {
            $file = fopen('php://output', 'w');

            // ستون‌های CSV
            fputcsv($file, ['شناسه', 'ایمیل/شماره', 'نوع', 'وضعیت', 'IP', 'مرورگر', 'سیستم عامل', 'دستگاه', 'تاریخ ثبت نام']);

            foreach ($subscribers as $subscriber) {
                fputcsv($file, [
                    $subscriber->id,
                    $subscriber->contact,
                    $subscriber->contact_type == 'email' ? 'ایمیل' : 'شماره موبایل',
                    $subscriber->is_active ? 'فعال' : 'غیرفعال',
                    $subscriber->ip_address ?? '-',
                    $subscriber->browser ?? '-',
                    $subscriber->os ?? '-',
                    $subscriber->device_type ?? '-',
                    jdate($subscriber->created_at)->format('Y/m-d H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
