<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:products.reviews');
    }
    public function index()
    {
        $reviews = Review::filter()->paginate(20);

        return view('back.reviews.index', compact('reviews'));
    }

    public function show(Review $review)
    {
        return view('back.reviews.show', compact('review'))->render();
    }

    public function destroy(Review $review)
    {
        $adminName = auth('adminPanel')->user()->full_name ?? auth('adminPanel')->user()->name ?? 'مدیر';
        $productTitle = $review->product->title ?? "#{$review->product_id}";
        $reviewExcerpt = mb_substr($review->body, 0, 50);

        $review->delete();
        $review->product->refreshRating();

        // ثبت لاگ حذف نظر
        activity()
            ->performedOn($review->product)
            ->causedBy(auth('adminPanel')->user())
            ->event('deleted')
            ->withProperties([
                'action' => 'delete_review',
                'product_title' => $productTitle,
                'product_id' => $review->product_id,
                'review_id' => $review->id,
                'review_title' => $review->title,
                'review_excerpt' => $reviewExcerpt,
                'rating' => $review->rating,
                'ip' => request()->ip()
            ])
            ->log("{$adminName} نظر «{$reviewExcerpt}» را برای محصول «{$productTitle}» حذف کرد");

        return response('success');
    }

    public function update(Review $review, Request $request)
    {
        $data = $this->validate($request, [
            'title'       => 'required|string',
            'body'        => 'required|string|max:1000',
            'rating'      => 'required|between:1,5',
            'suggest'     => 'nullable|in:yes,no,not_sure',
            'status'      => 'in:pending,accepted,rejected',
        ]);

        // ذخیره مقادیر قدیمی برای لاگ
        $oldTitle = $review->title;
        $oldBody = $review->body;
        $oldRating = $review->rating;
        $oldStatus = $review->status;
        $oldSuggest = $review->suggest;

        $review->update($data);

        // ذخیره نقاط قوت و ضعف قبلی
        $oldAdvantages = $review->points()->where('type', 'positive')->pluck('text')->toArray();
        $oldDisadvantages = $review->points()->where('type', 'negative')->pluck('text')->toArray();

        $review->points()->delete();

        $request->validate([
            'review.advantages.*' => 'string',
            'review.disadvantages.*' => 'string',
        ]);

        $advantages = $request->input('review.advantages');
        $advantagesList = [];
        if ($advantages) {
            foreach ($advantages as $advantage) {
                $review->points()->create([
                    'text' => $advantage,
                    'type' => 'positive',
                ]);
                $advantagesList[] = $advantage;
            }
        }

        $disadvantages = $request->input('review.disadvantages');
        $disadvantagesList = [];
        if ($disadvantages) {
            foreach ($disadvantages as $advantage) {
                $review->points()->create([
                    'text' => $advantage,
                    'type' => 'negative',
                ]);
                $disadvantagesList[] = $advantage;
            }
        }

        $review->product->refreshRating();

        // ثبت لاگ ویرایش نظر
        $adminName = auth('adminPanel')->user()->full_name ?? auth('adminPanel')->user()->name ?? 'مدیر';
        $productTitle = $review->product->title ?? "#{$review->product_id}";
        $reviewExcerpt = mb_substr($review->body, 0, 50);

        $oldData = [];
        $newData = [];

        if ($oldTitle != $review->title) {
            $oldData['عنوان'] = $oldTitle;
            $newData['عنوان'] = $review->title;
        }
        if ($oldBody != $review->body) {
            $oldData['متن'] = mb_substr($oldBody, 0, 50);
            $newData['متن'] = mb_substr($review->body, 0, 50);
        }
        if ($oldRating != $review->rating) {
            $oldData['امتیاز'] = $oldRating . ' از 5';
            $newData['امتیاز'] = $review->rating . ' از 5';
        }
        if ($oldStatus != $review->status) {
            $statusMap = ['pending' => 'در انتظار', 'accepted' => 'تایید شده', 'rejected' => 'رد شده'];
            $oldData['وضعیت'] = $statusMap[$oldStatus] ?? $oldStatus;
            $newData['وضعیت'] = $statusMap[$review->status] ?? $review->status;
        }
        if ($oldSuggest != $review->suggest) {
            $suggestMap = ['yes' => 'بله', 'no' => 'خیر', 'not_sure' => 'مطمئن نیستم'];
            $oldData['توصیه به خرید'] = $suggestMap[$oldSuggest] ?? $oldSuggest;
            $newData['توصیه به خرید'] = $suggestMap[$review->suggest] ?? $review->suggest;
        }

        // بررسی تغییرات نقاط قوت
        if (!empty($oldAdvantages) || !empty($advantagesList)) {
            $oldAdvText = implode('، ', $oldAdvantages);
            $newAdvText = implode('، ', $advantagesList);
            if ($oldAdvText != $newAdvText) {
                $oldData['نقاط قوت'] = $oldAdvText ?: 'ندارد';
                $newData['نقاط قوت'] = $newAdvText ?: 'ندارد';
            }
        }

        // بررسی تغییرات نقاط ضعف
        if (!empty($oldDisadvantages) || !empty($disadvantagesList)) {
            $oldDisText = implode('، ', $oldDisadvantages);
            $newDisText = implode('، ', $disadvantagesList);
            if ($oldDisText != $newDisText) {
                $oldData['نقاط ضعف'] = $oldDisText ?: 'ندارد';
                $newData['نقاط ضعف'] = $newDisText ?: 'ندارد';
            }
        }

        if (!empty($oldData)) {
            activity()
                ->performedOn($review->product)
                ->causedBy(auth('adminPanel')->user())
                ->event('updated')
                ->withProperties([
                    'action' => 'update_review',
                    'product_title' => $productTitle,
                    'product_id' => $review->product_id,
                    'review_id' => $review->id,
                    'old' => $oldData,
                    'attributes' => $newData,
                    'ip' => request()->ip()
                ])
                ->log("{$adminName} نظر «{$reviewExcerpt}» را برای محصول «{$productTitle}» ویرایش کرد");
        }

        return response($review);
    }
}
