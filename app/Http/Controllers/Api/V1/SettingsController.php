<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Settings\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function __construct(
        protected SettingsService $settingsService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $storeId = $request->store_id;

        return response()->json([
            'settings' => $this->settingsService->getAll($storeId),
            'grouped' => $this->settingsService->getByGroup($storeId),
        ]);
    }

    public function groups(): JsonResponse
    {
        return response()->json(
            \App\Models\SettingGroup::orderBy('sort_order')->get()
        );
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'store_id' => 'nullable|exists:stores,id',
        ]);

        $this->settingsService->updateMany(
            $validated['settings'],
            $validated['store_id'] ?? null
        );

        return response()->json([
            'message' => 'Settings updated successfully',
            'settings' => $this->settingsService->getAll($validated['store_id'] ?? null),
        ]);
    }

    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => 'required|image|max:2048',
            'store_id' => 'nullable|exists:stores,id',
        ]);

        // Delete old logo if exists
        $oldLogo = $this->settingsService->get('shop_logo', null, $request->store_id);
        if ($oldLogo) {
            Storage::disk('public')->delete($oldLogo);
        }

        $path = $request->file('logo')->store('logos', 'public');

        $this->settingsService->set('shop_logo', $path, $request->store_id);

        return response()->json([
            'message' => 'Logo uploaded successfully',
            'path' => $path,
            'url' => asset('storage/' . $path),
        ]);
    }

    public function public(Request $request): JsonResponse
    {
        return response()->json(
            $this->settingsService->getPublicSettings($request->store_id)
        );
    }
}
