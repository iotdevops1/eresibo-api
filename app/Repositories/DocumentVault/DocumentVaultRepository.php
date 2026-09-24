<?php
namespace App\Repositories\DocumentVault;
use App\Models\DocumentVaultDocument;
use App\Repositories\BaseRepository;
class DocumentVaultRepository extends BaseRepository {
    public function __construct(DocumentVaultDocument $model){$this->model=$model;}
    public function firstOrCreateForSource(int $userId,string $type,string $sourceUuid,array $data):DocumentVaultDocument {
        return $this->model->newQuery()->firstOrCreate(['user_id'=>$userId,'document_type'=>$type,'source_uuid'=>$sourceUuid],$data);
    }
    public function paginateForUser(int $userId,array $filters=[]){
        $query=$this->model->newQuery()->where('user_id',$userId)->with('merchant');
        $status=$filters['status']??'ACTIVE';
        if($status==='ACTIVE')$query->whereNull('archived_at');
        if($status==='ARCHIVED')$query->whereNotNull('archived_at');
        if(!empty($filters['search'])){$search='%'.$filters['search'].'%';$query->where(fn($q)=>$q->where('title','like',$search)->orWhere('reference','like',$search)->orWhere('document_type','like',$search));}
        return $query->orderByDesc('document_date')->orderByDesc('id')->paginate($filters['per_page']??20);
    }
    public function findForUser(string $uuid,int $userId):?DocumentVaultDocument {
        if(!empty($filters['merchant_uuid']))$query->whereHas('merchant',fn($merchantQuery)=>$merchantQuery->where('uuid',$filters['merchant_uuid']));
        return $this->model->newQuery()->where('uuid',$uuid)->where('user_id',$userId)->with('merchant')->first();
    }
    public function countsForUser(int $userId):array {
        $query=$this->model->newQuery()->where('user_id',$userId);
        return ['active'=>(clone $query)->whereNull('archived_at')->count(),'archived'=>(clone $query)->whereNotNull('archived_at')->count(),'all'=>$query->count()];
    }
}
