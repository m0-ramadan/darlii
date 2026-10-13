<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePropertyRequest;
use App\Models\Currency;
use App\Models\Property;
use App\Modules\Property\Application\Actions\CreatePropertyAction;
use App\Modules\Property\Application\Actions\SubmitPropertyAction;

class PropertyController extends Controller
{
    public function index()
    {
        return view('dashboard.properties.index', [
            'properties' => Property::with(['translations', 'category', 'location'])
                ->where('owner_id', auth()->id())
                ->latest()
                ->paginate(15),
        ]);
    }

    public function create()
    {
        return view('dashboard.properties.create', [
            'currencies' => Currency::where('is_active', true)->get(),
        ]);
    }

    public function store(StorePropertyRequest $request, CreatePropertyAction $action)
    {
        $p = $action->execute($request->validated(), $request->user()->id);

        // العميل العادي لا يظهر عقاره في الموقع مباشرة بل يخضع لمراجعة وتأكيد الأدمن
        $user = $request->user();
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

    public function submit(string $locale, Property $property, SubmitPropertyAction $action)
    {
        $this->authorize('update', $property);
        $action->execute($property);

        return back()->with('success', 'تم إرسال العقار لمراجعة وتأكيد الإدارة.');
    }
}
