<?php
namespace App\Services\Wallet;

use App\Models\Employee;
use App\Models\Merchant;
use App\Models\User;
use App\Repositories\Wallet\WalletRepository;
use Illuminate\Validation\ValidationException;

class WalletBalanceService {
    public function __construct(protected WalletRepository $walletRepository) {}

    public function employee(User $user): array {
        $employee=Employee::query()->where('user_id',$user->id)->first();
        if(! $employee) throw ValidationException::withMessages([
            'employee'=>['Employee profile not found.'],
        ]);

        return [
            'wallet'=>$this->walletRepository->findUserWallet($user->id),
            'employee'=>$employee,
            'merchant'=>null,
        ];
    }

    public function employer(User $user): array {
        return [
            'wallet'=>$this->walletRepository->findUserWallet($user->id),
            'employee'=>null,
            'merchant'=>null,
        ];
    }

    public function merchant(User $user): array {
        if(! $user->merchant_id) throw ValidationException::withMessages([
            'merchant'=>['Employer is not assigned to a merchant.'],
        ]);

        $merchant=Merchant::query()->find($user->merchant_id);
        if(! $merchant) throw ValidationException::withMessages([
            'merchant'=>['Merchant not found.'],
        ]);

        return [
            'wallet'=>$this->walletRepository->findMerchantWallet($merchant->id),
            'employee'=>null,
            'merchant'=>$merchant,
        ];
    }
}
