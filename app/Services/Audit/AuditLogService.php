<?php
namespace App\Services\Audit;
use App\Models\AuditLog;
use App\Models\User;
class AuditLogService {
 public function index(User $u,array $f){$q=AuditLog::query()->where('merchant_id',$u->merchant_id)->with(['actor.role'])->orderByDesc('occurred_at');if($t=$f['event_type']??null)$q->where('event_type',$t);return $q->paginate($f['per_page']??30);}
 public function record(?User $actor,string $type,string $event,string $description,?string $subjectType=null,?string $subjectReference=null,array $metadata=[]):AuditLog{return AuditLog::create(['merchant_id'=>$actor?->merchant_id,'actor_user_id'=>$actor?->id,'event_type'=>$type,'event'=>$event,'description'=>$description,'subject_type'=>$subjectType,'subject_reference'=>$subjectReference,'metadata'=>$metadata,'occurred_at'=>now()]);}
}
