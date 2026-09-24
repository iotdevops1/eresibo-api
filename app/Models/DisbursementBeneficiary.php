<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class DisbursementBeneficiary extends Model {
 use HasUuids; public const STATUS_PENDING=1, STATUS_RELEASED=2;
 protected $fillable=['uuid','disbursement_id','beneficiary_name','wallet_uuid','email','reference_no','purpose','amount_minor_units','amount_major_units','status','wallet_transaction_id','released_at'];
 protected $casts=['amount_minor_units'=>'integer','amount_major_units'=>'decimal:2','released_at'=>'datetime'];
 public function uniqueIds():array{return ['uuid'];} public function disbursement():BelongsTo{return $this->belongsTo(Disbursement::class);} public function walletTransaction():BelongsTo{return $this->belongsTo(WalletTransaction::class);}
}
