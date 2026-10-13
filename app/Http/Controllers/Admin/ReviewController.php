<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminPanel;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Property;
use App\Models\Review;
use App\Models\User;
use App\Services\Admin\AuditLogger;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;

/**
 * التقييمات: مراجعة آراء العملاء على العقارات والمشروعات، اعتمادها أو
 * رفضها ثم حذفها.
 */
class ReviewController extends Controller
{    use InteractsWithAdminPanel;

    // ------------------------------------------------------------- القائمة
    public function index(Request $request)
    {
        $this->authorizePermission('review.moderate');

        $reviews = Review::query()
            ->with(['reviewable' => function (MorphTo $morphTo): void {
                $morphTo->morphWith([
                    Property::class => ['translations'],
                    Project::class => ['translations'],
                ]);
            }])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('rating'), fn ($query) => $query->where('rating', (int) $request->input('rating')))
            ->latest()
            ->paginate((int) setting('general.items_per_page', 15))
            ->withQueryString();

        return view('admin.reviews.index', [
            'reviews' => $reviews,
            'filters' => $request->only(['status', 'rating']),
            'statusLabels' => $this->statusLabels(),
            'userNames' => $this->userNames($reviews->pluck('user_id')->all()),
            'targetLabels' => $reviews->getCollection()
                ->mapWithKeys(fn (Review $review) => [$review->id => $this->reviewableLabel($review)])
                ->all(),
            'title' => 'التقييمات',
            'subtitle' => 'مراجعة واعتماد آراء العملاء على العقارات والمشروعات',
        ]);
    }

    // -------------------------------------------------------------- الاعتماد
    public function approve(Review $review)
    {
        $this->authorizePermission('review.moderate');

        $old = $review->status;
        $review->update(['status' => 'approved', 'approved_at' => now()]);

        AuditLogger::log('review.approved', $review, ['status' => $old], ['status' => 'approved']);

        return back()->with('success', 'تم اعتماد التقييم ونشره.');
    }

    // ---------------------------------------------------------------- الرفض
    public function reject(Review $review)
    {
        $this->authorizePermission('review.moderate');

        $old = $review->status;
        $review->update(['status' => 'rejected', 'approved_at' => null]);

        AuditLogger::log('review.rejected', $review, ['status' => $old], ['status' => 'rejected']);

        return back()->with('success', 'تم رفض التقييم.');
    }

    // --------------------------------------------------------------- الحذف
    public function destroy(Review $review)
    {
        $this->authorizePermission('review.moderate');

        AuditLogger::log('review.deleted', $review, [
            'reviewable_type' => $review->reviewable_type,
            'reviewable_id' => $review->reviewable_id,
        ]);
        $review->delete();

        return back()->with('success', 'تم حذف التقييم.');
    }

    // ------------------------------------------------------------- داخلي
    /** الوصف العربي للعنصر الذي تم تقييمه. */
    private function reviewableLabel(Review $review): string
    {
        $target = $review->reviewable;

        if (! $target) {
            return '—';
        }

        if ($target instanceof Property) {
            return $target->displayName('ar');
        }

        if ($target instanceof Project) {
            return (string) ($target->translation('ar')?->name ?: $target->reference_code);
        }

        if ($target instanceof User) {
            return (string) $target->name;
        }

        return class_basename($target) . ' #' . $review->reviewable_id;
    }

    /**
     * @param  array<int, int|null>  $ids
     * @return array<int, string>
     */
    private function userNames(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if ($ids === []) {
            return [];
        }

        return User::query()->whereIn('id', $ids)->pluck('name', 'id')->all();
    }

    /** @return array<string, string> */
    private function statusLabels(): array
    {
        return [
            'pending' => 'بانتظار المراجعة',
            'approved' => 'معتمد',
            'rejected' => 'مرفوض',
        ];
    }
}
