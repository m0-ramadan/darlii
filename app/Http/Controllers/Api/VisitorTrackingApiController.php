<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Analytics\VisitorTrackerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VisitorTrackingApiController extends Controller
{
    public function __construct(
        protected VisitorTrackerService $trackerService
    ) {}

    /**
     * تسجيل زيارة الزائر مع تفاصيل جهازه وموقعه.
     */
    public function track(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'visitor_id' => ['nullable', 'string', 'max:64'],
            'url' => ['nullable', 'string', 'max:500'],
            'page_title' => ['nullable', 'string', 'max:255'],
            'referrer' => ['nullable', 'string', 'max:500'],
            'screen_resolution' => ['nullable', 'string', 'max:30'],
            'language' => ['nullable', 'string', 'max:20'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'accuracy' => ['nullable', 'numeric'],
            'user_agent' => ['nullable', 'string'],
        ]);

        $visitor = $this->trackerService->track($request, $payload);

        return response()->json([
            'success' => true,
            'visitor_id' => $visitor->visitor_id,
            'governorate' => $visitor->governorate,
            'city_or_markaz' => $visitor->city_or_markaz,
            'location' => $visitor->fullLocation(),
        ]);
    }

    /**
     * تحديث الموقع الجغرافي عند سماح المستخدم بموقع الـ GPS من المتصفح.
     */
    public function updateLocation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'visitor_id' => ['required', 'string', 'max:64'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric'],
        ]);

        $visitor = $this->trackerService->updateGpsLocation(
            $validated['visitor_id'],
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            isset($validated['accuracy']) ? (int) $validated['accuracy'] : null
        );

        if (!$visitor) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على سجل الزائر',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'governorate' => $visitor->governorate,
            'city_or_markaz' => $visitor->city_or_markaz,
            'location' => $visitor->fullLocation(),
        ]);
    }
}

