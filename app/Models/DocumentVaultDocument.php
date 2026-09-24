<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
class DocumentVaultDocument extends Model {
    use HasUuids;
    public const TYPE_PAYSLIP='PAYSLIP';
    public const TYPE_RECEIPT='RECEIPT';
    protected $fillable=['uuid','user_id','merchant_id','document_type','source_uuid','title','reference','document_date','archived_at'];
    protected $casts=['document_date'=>'datetime','archived_at'=>'datetime'];
    public function uniqueIds():array{return ['uuid'];}
    public function merchant(){return $this->belongsTo(Merchant::class);}
    public function user(){return $this->belongsTo(User::class);}
}
