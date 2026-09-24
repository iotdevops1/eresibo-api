<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class AuditLogResource extends JsonResource {public function toArray(Request $r):array{return ['uuid'=>$this->uuid,'event_type'=>$this->event_type,'event'=>$this->event,'subject'=>['type'=>$this->subject_type,'reference'=>$this->subject_reference],'description'=>$this->description,'actor'=>$this->actor?['uuid'=>$this->actor->uuid,'name'=>$this->actor->name,'role'=>$this->actor->role?->code]:null,'metadata'=>$this->metadata,'occurred_at'=>$this->occurred_at?->toISOString()];}}
