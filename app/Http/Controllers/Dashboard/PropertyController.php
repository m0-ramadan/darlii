<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePropertyRequest;
use App\Models\Currency;
use App\Models\Property;
use App\Modules\Property\Application\Actions\CreatePropertyAction;
use App\Modules\Property\Application\Actions\SubmitPropertyAction;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $query = Property::with(['translations', 'category', 'location'])
            ->where('owner_id', auth()->id());

        if ($status === 'published') {
            $query->where('status', 'published');
        } elseif ($status === 'sold') {
            $query->where(function ($q) {
                $q->where('status', 'sold')->orWhereNotNull('sold_at');
            });
        } elseif ($status === 'pending_review') {
            $query->where('status', 'pending_review');
        }

        $properties = $query->latest()->paginate(15)->withQueryString();

        return view('dashboard.properties.index', compact('properties', 'status'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        if ($user && $user->status === 'pending') {
            return redirect()
                ->route('dashboard.index', ['locale' => app()->getLocale()])
                ->with('status_warning', 'حسابك قيد المراجعة والاعتماد من قبل إدارة منصة دارلي. لا يمكنك إضافة عقارات جديدة حتى يتم تفعيل حسابك من قبل الإدارة.');
        }

        return view('dashboard.properties.create', [
            'currencies' => Currency::where('is_active', true)->get(),
        ]);
    }

    public function store(StorePropertyRequest $request, CreatePropertyAction $action)
    {
        $user = $request->user();
        if ($user && $user->status === 'pending') {
            return redirect()
                ->route('dashboard.index', ['locale' => app()->getLocale()])
                ->with('status_warning', 'حسابك قيد المراجعة والاعتماد من قبل إدارة منصة دارلي. لا يمكنك إضافة عقارات جديدة حتى يتم تفعيل حسابك من قبل الإدارة.');
        }

        $p = $action->execute($request->validated(), $user->id);

        if ($request->hasFile('floor_plan_image') || filled($request->input('floor_plan_name')) || filled($request->input('floor_plan_description'))) {
            $imagePath = null;
            if ($request->hasFile('floor_plan_image') && $request->file('floor_plan_image')->isValid()) {
                $imagePath = $request->file('floor_plan_image')->store('properties/' . $p->id . '/floor_plans/' . now()->format('Y/m'), 'public');
            }
            $p->setFloorPlans([[
                'name' => $request->input('floor_plan_name') ?: 'المخطط الهندسي للشقة',
                'bedrooms' => $p->bedrooms,
                'bathrooms' => $p->bathrooms,
                'area' => $p->area,
                'image' => $imagePath,
                'description' => $request->input('floor_plan_description'),
            ]]);
        }

        // العميل العادي لا يظهر عقاره في الموقع مباشرة بل يخضع لمراجعة وتأكيد الأدمن
        $isClient = $user && ! $user->hasRole('agent') && ! $user->hasRole('admin');

        if ($isClient) {
            $p->update(['status' => 'pending_review']);
            $msg = 'تم إرسال العقار (' . $p->reference_code . ') بنجاح، وهو الآن قيد مراجعة وتأكيد الإدارة قبل النشر بالموقع.';
        } else {
            $msg = 'تم إنشاء العقار بنجاح (' . $p->reference_code . ').';
        }

        return redirect()
            ->route('dashboard.properties.index', ['locale' => app()->getLocale()])
            ->with('success', $msg);
    }

    public function submit(...$args)
    {
        $property = collect($args)->first(fn($a) => $a instanceof Property);
        $action = collect($args)->first(fn($a) => $a instanceof SubmitPropertyAction) ?? app(SubmitPropertyAction::class);
        $this->authorizeOwnerOrAdmin($property);
        $action->execute($property);

        return back()->with('success', 'تم إرسال العقار لمراجعة وتأكيد الإدارة.');
    }

    public function markSold(Request $request, ...$args)
    {
        $property = collect($args)->first(fn($a) => $a instanceof Property);
        $this->authorizeOwnerOrAdmin($property);

        $property->update([
            'status' => 'sold',
            'sold_at' => now(),
        ]);

        return back()->with('success', 'تم تحديث حالة العقار بنجاح إلى: تم البيع ✓');
    }

    public function markAvailable(Request $request, ...$args)
    {
        $property = collect($args)->first(fn($a) => $a instanceof Property);
        $this->authorizeOwnerOrAdmin($property);

        $property->update([
            'status' => 'published',
            'sold_at' => null,
        ]);

        return back()->with('success', 'تم إعادة تفعيل عرض العقار بالموقع بنجاح.');
    }

    private function authorizeOwnerOrAdmin(Property $property): void
    {
        $user = auth()->user();
        if (! $user || ($property->owner_id !== $user->id && ! $user->isAdmin())) {
            abort(403, 'غير مصرح لك بتعديل هذا العقار.');
        }
    }
}
