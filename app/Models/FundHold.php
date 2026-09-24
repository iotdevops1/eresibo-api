<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class FundHold extends Model {
 use HasUuids; public const STATUS_HELD=1, STATUS_RELEASED=2, STATUS_RETURNED=3;
 protected $fillable=['uuid','merchant_id','created_by_user_id','source_wallet_id','destination_wallet_id','document_reference','source_type','description','amount_minor_units','amount_major_units','currency','status','scheduled_release_at','released_at','returned_at'];
 protected $casts=['amount_minor_units'=>'integer','amount_major_units'=>'decimal:2','scheduled_release_at'=>'datetime','released_at'=>'datetime','returned_at'=>'datetime'];
 public function uniqueIds():array{return ['uuid'];} public function sourceWallet():BelongsTo{return $this->belongsTo(Wallet::class,'source_wallet_id');} public function destinationWallet():BelongsTo{return $this->belongsTo(Wallet::class,'destination_wallet_id');}
}
