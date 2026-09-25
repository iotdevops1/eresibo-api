<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
class Payslip extends Model {
    use HasUuids;
    public const STATUS_PENDING_ACKNOWLEDGEMENT=1;
    public const STATUS_ACKNOWLEDGED=2;
    protected $fillable=['uuid','merchant_id','employee_id','issued_by_user_id','source_wallet_id','destination_wallet_id','wallet_transaction_id','receipt_id','pay_period_start','pay_period_end','pay_date','note','currency','gross_amount_minor_units','deduction_amount_minor_units','net_amount_minor_units','gross_amount_major_units','deduction_amount_major_units','net_amount_major_units','status','acknowledged_at'];
    protected $casts=['pay_period_start'=>'date','pay_period_end'=>'date','pay_date'=>'date','gross_amount_minor_units'=>'integer','deduction_amount_minor_units'=>'integer','net_amount_minor_units'=>'integer','gross_amount_major_units'=>'decimal:2','deduction_amount_major_units'=>'decimal:2','net_amount_major_units'=>'decimal:2','status'=>'integer','acknowledged_at'=>'datetime'];
    public function uniqueIds():array{return ['uuid'];}
    public function getReferenceAttribute(): string
    {
        return 'PAYSLIP-'.$this->uuid;
    }
    public function getVerificationUrlAttribute(): string
    {
        return rtrim((string) config('eresibo.portal_url'), '/').'/verify?'.http_build_query(
            ['document' => $this->reference], '', '&', PHP_QUERY_RFC3986,
        );
    }
    public function employee(){return $this->belongsTo(Employee::class);}
    public function lines(){return $this->hasMany(PayslipLine::class)->orderBy('sort_order');}
    public function walletTransaction(){return $this->belongsTo(WalletTransaction::class);}
    public function merchant(){return $this->belongsTo(Merchant::class);}
    public function receipt(){return $this->belongsTo(Receipt::class);}
}
