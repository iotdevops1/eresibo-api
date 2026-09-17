<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
class PayslipLine extends Model {
    use HasUuids;
    public const TYPE_EARNING = 'EARNING';
    public const TYPE_DEDUCTION = 'DEDUCTION';
    protected $fillable = ['uuid','payslip_id','line_type','description','amount_minor_units','amount_major_units','sort_order'];
    protected $casts = ['amount_minor_units'=>'integer','amount_major_units'=>'decimal:2','sort_order'=>'integer'];
    public function uniqueIds(): array { return ['uuid']; }
}
