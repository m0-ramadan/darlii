<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminPanel;
use App\Http\Controllers\Controller;
use App\Models\CustomerRequest;
use App\Models\Location;
use App\Services\Admin\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class CustomerRequestController extends Controller
{
    use InteractsWithAdminPanel;

    public function index(Request $request)
    {
        $requests = $this->filteredQuery($request)
            ->with(['location.parent.translations', 'location.translations', 'creator'])
            ->latest()->paginate(20)->withQueryString();

        return view('admin.customer-requests.index', [
            'requests' => $requests,
            'locations' => $this->locationOptions(),
            'statuses' => CustomerRequest::STATUSES,
            'counts' => $this->statusCounts(),
            'locationCounts' => $this->locationCounts(),
            'filters' => $request->only(['q', 'status', 'location_id', 'from', 'to']),
            'title' => 'طلبات العملاء',
            'subtitle' => 'احتياجات العملاء ومتابعة حالتها — متاح لكل حسابات الداشبورد',
        ]);
    }

    public function create()
    {
        return view('admin.customer-requests.form', [
            'customerRequest' => new CustomerRequest(['status' => 'new']),
            'locations' => $this->locationOptions(),
            'statuses' => CustomerRequest::STATUSES,
            'title' => 'إضافة طلب عميل',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['status'] = 'new';
        $data['created_by'] = $this->admin()->id;

        $customerRequest = CustomerRequest::create($data);
        AuditLogger::log('customer_request.created', $customerRequest, [], $customerRequest->only(['customer_name', 'phone', 'location_id']));

        return redirect()->route('admin.customer-requests.index')->with('success', 'تم تسجيل طلب العميل.');
    }

    public function edit(CustomerRequest $customerRequest)
    {
        return view('admin.customer-requests.form', [
            'customerRequest' => $customerRequest,
            'locations' => $this->locationOptions(),
            'statuses' => CustomerRequest::STATUSES,
            'title' => 'تعديل طلب عميل',
        ]);
    }

    public function update(Request $request, CustomerRequest $customerRequest)
    {
        $data = $this->validated($request);
        $data['updated_by'] = $this->admin()->id;
        $old = $customerRequest->only(['customer_name', 'phone', 'location_id', 'status']);
        $customerRequest->update($data);
        AuditLogger::log('customer_request.updated', $customerRequest, $old, $customerRequest->fresh()->only(array_keys($old)));

        return redirect()->route('admin.customer-requests.index')->with('success', 'تم تحديث طلب العميل.');
    }

    public function updateStatus(Request $request, CustomerRequest $customerRequest)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(CustomerRequest::STATUSES))],
        ]);

        $old = $customerRequest->status;
        $customerRequest->update(['status' => $data['status'], 'updated_by' => $this->admin()->id]);
        AuditLogger::log('customer_request.status_changed', $customerRequest, ['status' => $old], ['status' => $data['status']]);

        return back()->with('success', 'تم تحديث حالة الطلب.');
    }

    public function destroy(CustomerRequest $customerRequest)
    {
        abort_unless($this->admin()->isAdmin() || $this->admin()->id === $customerRequest->created_by, 403, 'يمكن للإدارة أو صاحب الطلب حذفه.');

        AuditLogger::log('customer_request.deleted', $customerRequest, $customerRequest->only(['customer_name', 'phone']));
        $customerRequest->delete();

        return back()->with('success', 'تم حذف الطلب.');
    }

    public function stats(Request $request)
    {
        $from = $request->filled('from') ? Carbon::parse($request->input('from'))->startOfDay() : now()->subMonths(5)->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->input('to'))->endOfDay() : now()->endOfDay();
        $base = CustomerRequest::query()->whereBetween('created_at', [$from, $to]);

        $byStatus = (clone $base)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $byLocation = (clone $base)->with('location.translations', 'location.parent.translations')
            ->selectRaw('location_id, COUNT(*) as total')->groupBy('location_id')->orderByDesc('total')->limit(20)->get();

        $monthly = [];
        $cursor = $from->copy()->startOfMonth();
        while ($cursor->lte($to)) {
            $start = $cursor->copy()->startOfMonth();
            $end = $cursor->copy()->endOfMonth();
            $monthly[] = [
                'label' => $start->format('m/Y'),
                'total' => (clone $base)->whereBetween('created_at', [$start, $end])->count(),
            ];
            $cursor->addMonth();
        }

        return view('admin.customer-requests.stats', [
            'from' => $from->toDateString(), 'to' => $to->toDateString(),
            'statuses' => CustomerRequest::STATUSES, 'byStatus' => $byStatus,
            'byLocation' => $byLocation, 'monthly' => $monthly,
            'total' => (clone $base)->count(), 'counts' => $this->statusCounts(),
            'title' => 'إحصائيات طلبات العملاء',
            'subtitle' => 'توزيع الطلبات حسب الحالة والموقع والشهر',
        ]);
    }

    private function filteredQuery(Request $request)
    {
        return CustomerRequest::query()
            ->when($request->filled('q'), function ($q) use ($request): void {
                $term = trim((string) $request->input('q'));
                $q->where(fn ($x) => $x->where('customer_name', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('need', 'like', "%{$term}%"));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('location_id'), function ($q) use ($request): void {
                $id = (int) $request->input('location_id');
                $ids = Location::query()->where('parent_id', $id)->pluck('id')->push($id)->all();
                $q->whereIn('location_id', $ids);
            })
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->input('to')));
    }

    private function statusCounts(): array
    {
        $counts = CustomerRequest::query()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status')->all();

        return collect(CustomerRequest::STATUSES)->mapWithKeys(fn ($label, $key) => [$key => (int) ($counts[$key] ?? 0)])->all();
    }

    private function locationCounts()
    {
        return CustomerRequest::query()->whereNotNull('location_id')
            ->with('location.translations', 'location.parent.translations')
            ->selectRaw('location_id, COUNT(*) as total')->groupBy('location_id')->orderByDesc('total')->limit(12)->get()
            ->map(fn (CustomerRequest $row) => ['id' => (int) $row->location_id, 'label' => $row->locationName(), 'total' => (int) $row->total]);
    }

    private function locationOptions()
    {
        return Location::query()->whereIn('type', ['city', 'district'])->where('status', true)
            ->with(['translations', 'parent.translations'])->get()
            ->map(function (Location $location): array {
                $name = $location->translation()?->name ?: ('#' . $location->id);
                $parent = $location->parent?->translation()?->name;

                return ['id' => (int) $location->id, 'label' => $parent ? $parent . ' — ' . $name : $name];
            })->sortBy('label')->values();
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'need' => ['required', 'string', 'max:5000'],
            'location_id' => ['required', 'exists:locations,id'],
            'address_details' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(array_keys(CustomerRequest::STATUSES))],
            'notes' => ['nullable', 'string', 'max:3000'],
        ]);

        return [
            'customer_name' => $data['customer_name'],
            'phone' => $data['phone'],
            'need' => $data['need'],
            'location_id' => $data['location_id'],
            'address_details' => $data['address_details'] ?? null,
            'status' => $data['status'] ?? 'new',
            'notes' => $data['notes'] ?? null,
        ];
    }
}
