<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AuditLog extends Model {use HasUuids;public const EVENT_PAYSLIP='PAYSLIP',EVENT_EMPLOYEE='EMPLOYEE',EVENT_TRANSACTION='TRANSACTION',EVENT_ACKNOWLEDGEMENT='ACKNOWLEDGEMENT';protected $fillable=['uuid','merchant_id','actor_user_id','event_type','event','subject_type','subject_reference','description','metadata','ip_address','user_agent','occurred_at'];protected $casts=['metadata'=>'array','occurred_at'=>'datetime'];public function uniqueIds():array{return ['uuid'];}public function actor():BelongsTo{return $this->belongsTo(User::class,'actor_user_id');}}
