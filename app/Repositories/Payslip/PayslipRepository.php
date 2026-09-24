<?php
namespace App\Repositories\Payslip;

use App\Models\Payslip;
use App\Repositories\BaseRepository;

class PayslipRepository extends BaseRepository {
    public function __construct(Payslip $model) { $this->model=$model; }

    public function paginateByEmployeeUser(int $userId,array $filters=[]) {
        $query=$this->model->newQuery()
            ->whereHas('employee',fn($query)=>$query->where('user_id',$userId))
            ->with(['employee','lines','walletTransaction','receipt']);

        if(isset($filters['status'])) $query->where('status',$filters['status']);
        if(! empty($filters['pay_date'])) $query->whereDate('pay_date',$filters['pay_date']);

        return $query->orderByDesc('id')->paginate($filters['per_page']??20);
    }

    public function findByUuidForEmployeeUser(string $uuid,int $userId): ?Payslip {
        return $this->model->newQuery()
            ->where('uuid',$uuid)
            ->whereHas('employee',fn($query)=>$query->where('user_id',$userId))
            ->with(['employee','lines','walletTransaction','receipt'])
            ->first();
    }
}
