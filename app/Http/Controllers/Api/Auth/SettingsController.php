<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Settings\UpdateProfileRequest;
use App\Http\Requests\Settings\UpdateReceiptPreferencesRequest;
use App\Http\Resources\ReceiptPreferenceResource;
use App\Http\Resources\UserProfileResource;
use App\Services\Settings\UserSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends BaseApiController
{
    public function __construct(protected UserSettingsService $userSettingsService)
    {
    }

    public function profile(Request $request): JsonResponse
    {
        return $this->success(
            new UserProfileResource($this->userSettingsService->profile($request->user())),
            'Profile retrieved successfully.'
        );
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        return $this->success(
            new UserProfileResource(
                $this->userSettingsService->updateProfile($request->user(), $request->validated())
            ),
            'Profile updated successfully.'
        );
    }

    public function receiptPreferences(Request $request): JsonResponse
    {
        return $this->success(
            new ReceiptPreferenceResource(
                $this->userSettingsService->receiptPreferences($request->user())
            ),
            'Receipt preferences retrieved successfully.'
        );
    }

    public function updateReceiptPreferences(
        UpdateReceiptPreferencesRequest $request
    ): JsonResponse {
        return $this->success(
            new ReceiptPreferenceResource(
                $this->userSettingsService->updateReceiptPreferences(
                    $request->user(),
                    $request->validated()
                )
            ),
            'Receipt preferences updated successfully.'
        );
    }
}
