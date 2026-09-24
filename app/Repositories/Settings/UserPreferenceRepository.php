<?php

namespace App\Repositories\Settings;

use App\Models\UserPreference;
use App\Repositories\BaseRepository;

class UserPreferenceRepository extends BaseRepository
{
    public function __construct(UserPreference $model)
    {
        $this->model = $model;
    }

    public function firstOrCreateForUser(int $userId): UserPreference
    {
        return $this->model->newQuery()->firstOrCreate(
            ['user_id' => $userId],
            [
                'receipt_generation_mode' => UserPreference::RECEIPT_MODE_ASK,
                'default_receipt_type' => UserPreference::RECEIPT_TYPE_ERECEIPT,
                'locale' => 'en',
            ]
        );
    }
}
