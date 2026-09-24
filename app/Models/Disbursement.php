<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Disbursement extends Model {
 use HasUuids;
 public const STATUS_APPROVED=1, STATUS_RELEASED=2, STATUS_CANCELLED=3;
 public const POLICY_IMMEDIATE='IMMEDIATE', POLICY_AFTER_ACKNOWLEDGEMENT='AFTER_ACKNOWLEDGEMENT';
 protected $fillable=['uuid','merchant_id','created_by_user_id','program_type','program_name','note','release_policy','status','currency','total_amount_minor_units','total_amount_major_units','released_at','cancelled_at'];
 protected $casts=['total_amount_minor_units'=>'integer','total_amount_major_units'=>'decimal:2','released_at'=>'datetime','cancelled_at'=>'datetime'];
 public function uniqueIds():array{return ['uuid'];} public function merchant():BelongsTo{return $this->belongsTo(Merchant::class);} public function createdBy():BelongsTo{return $this->belongsTo(User::class,'created_by_user_id');} public function beneficiaries():HasMany{return $this->hasMany(DisbursementBeneficiary::class);}
}
