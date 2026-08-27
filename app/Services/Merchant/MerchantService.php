<?php

namespace App\Services\Merchant;

use App\Models\Merchant;
use App\Repositories\Merchant\MerchantRepository;
use Illuminate\Validation\ValidationException;

class MerchantService
{
    public function __construct(
        protected MerchantRepository $merchantRepository
    ) {
    }

    public function index(array $filters = [])
    {
        return $this->merchantRepository->paginate($filters);
    }

    public function show(string $uuid): Merchant
    {
        $merchant = $this->merchantRepository->findByUuid($uuid);

        if (! $merchant) {
            throw ValidationException::withMessages([
                'merchant' => [
                    'Merchant not found.'
                ],
            ]);
        }

        return $merchant;
    }

    public function store(array $data): Merchant
    {
        return $this->merchantRepository->create([
            'merchant_code' => $data['merchant_code'],
            'business_name' => $data['business_name'],
            'business_type' => $data['business_type'] ?? null,
            'email' => $data['email'] ?? null,
            'mobile' => $data['mobile'] ?? null,
            'address' => $data['address'] ?? null,
            'status' => $data['status'],
        ]);
    }

    public function update(
        Merchant $merchant,
        array $data
    ): Merchant {
        $this->merchantRepository->update(
            $merchant,
            $data
        );

        return $merchant->refresh();
    }

    public function destroy(Merchant $merchant): void
    {
        if ($merchant->employers()->exists()) {
            throw ValidationException::withMessages([
                'merchant' => [
                    'Cannot delete a merchant that has employers assigned.'
                ],
            ]);
        }

        $this->merchantRepository->update(
            $merchant,
            [
                'status' => Merchant::STATUS_INACTIVE,
            ]
        );

        $this->merchantRepository->delete($merchant);
    }
}