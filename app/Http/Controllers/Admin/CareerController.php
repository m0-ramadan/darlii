<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminPanel;
use App\Http\Controllers\Controller;
use App\Models\Career;
use App\Models\JobApplication;
use App\Models\Lead;
use App\Services\Admin\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CareerController extends Controller
{
    use InteractsWithAdminPanel;

    public function index(Request $request)
    {
        $this->authorizePermission('content.manage');

        $query = Career::query()
            ->with(['translations'])
            ->withCount(['applications']);

        if ($request->filled('q')) {
            $term = trim((string) $request->input('q'));
            $query->where(function ($q) use ($term) {
                $q->whereHas('translations', fn ($t) => $t->where('title', 'like', "%{$term}%")->orWhere('summary', 'like', "%{$term}%"))
                    ->orWhere('department', 'like', "%{$term}%")
                    ->orWhere('location', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('department')) {
            $query->where('department', $request->input('department'));
        }

        $careers = $query->latest('id')
            ->paginate((int) setting('general.items_per_page', 15))
            ->withQueryString();

        $stats = [
            'total' => Career::count(),
            'published' => Career::where('status', 'published')->count(),
            'draft' => Career::where('status', 'draft')->count(),
            'applications' => JobApplication::count() + Lead::where('source', 'career')->count(),
        ];

        $departments = Career::query()->whereNotNull('department')->distinct()->pluck('department')->filter();

        return view('admin.careers.index', [
            'careers' => $careers,
            'stats' => $stats,
            'departments' => $departments,
        ]);
    }

    public function create()
    {
        $this->authorizePermission('content.manage');

        return view('admin.careers.form', [
            'career' => new Career([
                'status' => 'published',
                'employment_type' => 'دوام كامل',
                'location' => 'مدينة بني سويف',
            ]),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePermission('content.manage');

        $data = $request->validate([
            'title_ar' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'employment_type' => ['nullable', 'string', 'max:100'],
            'experience_level' => ['nullable', 'string', 'max:100'],
            'salary_min' => ['nullable', 'integer', 'min:0'],
            'salary_max' => ['nullable', 'integer', 'min:0'],
            'deadline' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['published', 'draft'])],
            'summary_ar' => ['nullable', 'string', 'max:2000'],
            'summary_en' => ['nullable', 'string', 'max:2000'],
            'description_ar' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
        ]);

        $career = DB::transaction(function () use ($data): Career {
            $career = Career::create([
                'department' => $data['department'] ?? null,
                'location' => $data['location'] ?? null,
                'employment_type' => $data['employment_type'] ?? null,
                'experience_level' => $data['experience_level'] ?? null,
                'salary_min' => $data['salary_min'] ?? null,
                'salary_max' => $data['salary_max'] ?? null,
                'deadline' => $data['deadline'] ?? null,
                'status' => $data['status'],
            ]);

            $baseSlug = Str::slug($data['title_en'] ?: $data['title_ar']) ?: 'job-' . Str::random(6);

            $career->translations()->create([
                'locale' => 'ar',
                'title' => $data['title_ar'],
                'slug' => $baseSlug . '-ar',
                'summary' => $data['summary_ar'] ?? null,
                'description' => $data['description_ar'] ?? null,
                'seo_title' => $data['title_ar'] . ' | وظائف دارلي',
                'seo_description' => Str::limit($data['summary_ar'] ?? $data['title_ar'], 160),
            ]);

            if (!empty($data['title_en'])) {
                $career->translations()->create([
                    'locale' => 'en',
                    'title' => $data['title_en'],
                    'slug' => $baseSlug,
                    'summary' => $data['summary_en'] ?? null,
                    'description' => $data['description_en'] ?? null,
                    'seo_title' => $data['title_en'] . ' | Darli Careers',
                    'seo_description' => Str::limit($data['summary_en'] ?? $data['title_en'], 160),
                ]);
            }

            AuditLogger::log('career.created', $career, [], ['title' => $data['title_ar']]);

            return $career;
        });

        return redirect()
            ->to(admin_route('admin.careers.index'))
            ->with('status', 'تمت إضافة الوظيفة بنجاح.');
    }

    public function edit(Career $career)
    {
        $this->authorizePermission('content.manage');

        $career->load('translations');

        return view('admin.careers.form', [
            'career' => $career,
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, Career $career): RedirectResponse
    {
        $this->authorizePermission('content.manage');

        $data = $request->validate([
            'title_ar' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'employment_type' => ['nullable', 'string', 'max:100'],
            'experience_level' => ['nullable', 'string', 'max:100'],
            'salary_min' => ['nullable', 'integer', 'min:0'],
            'salary_max' => ['nullable', 'integer', 'min:0'],
            'deadline' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['published', 'draft'])],
            'summary_ar' => ['nullable', 'string', 'max:2000'],
            'summary_en' => ['nullable', 'string', 'max:2000'],
            'description_ar' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
        ]);

        $old = $career->only(['status', 'department', 'location']);

        DB::transaction(function () use ($career, $data, $old): void {
            $career->update([
                'department' => $data['department'] ?? null,
                'location' => $data['location'] ?? null,
                'employment_type' => $data['employment_type'] ?? null,
                'experience_level' => $data['experience_level'] ?? null,
                'salary_min' => $data['salary_min'] ?? null,
                'salary_max' => $data['salary_max'] ?? null,
                'deadline' => $data['deadline'] ?? null,
                'status' => $data['status'],
            ]);

            $career->translations()->updateOrCreate(
                ['locale' => 'ar'],
                [
                    'title' => $data['title_ar'],
                    'summary' => $data['summary_ar'] ?? null,
                    'description' => $data['description_ar'] ?? null,
                    'seo_title' => $data['title_ar'] . ' | وظائف دارلي',
                    'seo_description' => Str::limit($data['summary_ar'] ?? $data['title_ar'], 160),
                ]
            );

            if (!empty($data['title_en'])) {
                $career->translations()->updateOrCreate(
                    ['locale' => 'en'],
                    [
                        'title' => $data['title_en'],
                        'summary' => $data['summary_en'] ?? null,
                        'description' => $data['description_en'] ?? null,
                        'seo_title' => $data['title_en'] . ' | Darli Careers',
                        'seo_description' => Str::limit($data['summary_en'] ?? $data['title_en'], 160),
                    ]
                );
            }

            AuditLogger::log('career.updated', $career, $old, $career->fresh()->only(array_keys($old)));
        });

        return redirect()
            ->to(admin_route('admin.careers.index'))
            ->with('status', 'تم تحديث بيانات الوظيفة بنجاح.');
    }

    public function toggleStatus(Career $career): RedirectResponse
    {
        $this->authorizePermission('content.manage');

        $newStatus = $career->status === 'published' ? 'draft' : 'published';
        $career->update(['status' => $newStatus]);

        AuditLogger::log('career.status', $career, [], ['status' => $newStatus]);

        $msg = $newStatus === 'published' ? 'تم نشر الوظيفة في الموقع.' : 'تم تحويل الوظيفة إلى مسودة.';

        return back()->with('status', $msg);
    }

    public function destroy(Career $career): RedirectResponse
    {
        $this->authorizePermission('content.manage');

        $title = $career->title;
        $career->delete();

        AuditLogger::log('career.deleted', null, ['title' => $title], []);

        return redirect()
            ->to(admin_route('admin.careers.index'))
            ->with('status', "تم حذف الوظيفة ({$title}) بنجاح.");
    }

    public function applications(Request $request)
    {
        $this->authorizePermission('content.manage');

        $applications = Lead::query()
            ->where('source', 'career')
            ->latest()
            ->paginate((int) setting('general.items_per_page', 20));

        return view('admin.careers.applications', [
            'applications' => $applications,
        ]);
    }
}

