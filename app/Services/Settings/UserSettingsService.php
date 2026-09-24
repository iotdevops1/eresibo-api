<?php

namespace App\Services\Settings;

use App\Models\User;
use App\Models\UserPreference;
use App\Repositories\Settings\UserPreferenceRepository;

class UserSettingsService
{
    public function __construct(
        protected UserPreferenceRepository $userPreferenceRepository,
    ) {
    }

    public function profile(User $user): User
    {
        return $user->refresh()->load(['role', 'preference']);
    }

    public function updateProfile(User $user, array $data): User
    {
        $user->update($data);

        return $this->profile($user);
    }

    public function receiptPreferences(User $user): UserPreference
    {
        return $this->userPreferenceRepository->firstOrCreateForUser($user->id);
    }

    public function updateReceiptPreferences(User $user, array $data): UserPreference
    {
        $preferences = $this->receiptPreferences($user);
        $preferences->update($data);

        return $preferences->refresh();
    }
}
