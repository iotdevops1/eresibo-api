<?php
namespace App\Services\DocumentVault;
use App\Models\DocumentVaultDocument;
use App\Models\Employee;
use App\Models\Payslip;
use App\Models\User;
use App\Repositories\DocumentVault\DocumentVaultRepository;
use Illuminate\Validation\ValidationException;
class DocumentVaultService {
    public function __construct(protected DocumentVaultRepository $documentVaultRepository){}
    public function index(User $user,array $filters):array{return $this->indexFor($user,$filters,fn()=> $this->syncEmployeeDocuments($user));}
    public function employerIndex(User $user,array $filters):array{return $this->indexFor($user,$filters,fn()=> $this->syncMerchantDocuments($user));}
    public function administratorIndex(User $user,array $filters):array{return $this->indexFor($user,$filters,fn()=> $this->syncPlatformDocuments($user));}
    public function show(User $user,string $uuid):DocumentVaultDocument{return $this->showFor($user,$uuid,fn()=> $this->syncEmployeeDocuments($user));}
    public function employerShow(User $user,string $uuid):DocumentVaultDocument{return $this->showFor($user,$uuid,fn()=> $this->syncMerchantDocuments($user));}
    public function administratorShow(User $user,string $uuid):DocumentVaultDocument{return $this->showFor($user,$uuid,fn()=> $this->syncPlatformDocuments($user));}
    public function setArchived(User $user,string $uuid,bool $archived):DocumentVaultDocument{return $this->setArchivedFor($user,$uuid,$archived,fn()=> $this->syncEmployeeDocuments($user));}
    public function employerSetArchived(User $user,string $uuid,bool $archived):DocumentVaultDocument{return $this->setArchivedFor($user,$uuid,$archived,fn()=> $this->syncMerchantDocuments($user));}
    public function administratorSetArchived(User $user,string $uuid,bool $archived):DocumentVaultDocument{return $this->setArchivedFor($user,$uuid,$archived,fn()=> $this->syncPlatformDocuments($user));}
    private function indexFor(User $user,array $filters,callable $sync):array {
        $sync();return ['documents'=>$this->documentVaultRepository->paginateForUser($user->id,$filters),'counts'=>$this->documentVaultRepository->countsForUser($user->id)];
    }
    private function showFor(User $user,string $uuid,callable $sync):DocumentVaultDocument {
        $sync();$document=$this->documentVaultRepository->findForUser($uuid,$user->id);
        if(!$document)throw ValidationException::withMessages(['document'=>['Document not found.']]);
        return $document;
    }
    private function setArchivedFor(User $user,string $uuid,bool $archived,callable $sync):DocumentVaultDocument {
        $document=$this->showFor($user,$uuid,$sync);$document->update(['archived_at'=>$archived?now():null]);return $document->refresh()->load('merchant');
    }
    private function syncEmployeeDocuments(User $user):void {
        $employee=Employee::query()->where('user_id',$user->id)->first();
        if(!$employee)throw ValidationException::withMessages(['employee'=>['Employee profile not found.']]);
        Payslip::query()->where('employee_id',$employee->id)->with(['receipt','merchant'])->orderBy('id')->each(fn(Payslip $payslip)=>$this->syncPayslip($user,$payslip));
    }
    private function syncMerchantDocuments(User $user):void {
        if(!$user->merchant_id)throw ValidationException::withMessages(['merchant'=>['Employer is not assigned to a merchant.']]);
        Payslip::query()->where('merchant_id',$user->merchant_id)->with(['receipt','merchant'])->orderBy('id')->each(fn(Payslip $payslip)=>$this->syncPayslip($user,$payslip));
    }
    private function syncPlatformDocuments(User $user):void {
        Payslip::query()->with(['receipt','merchant'])->orderBy('id')->each(fn(Payslip $payslip)=>$this->syncPayslip($user,$payslip));
    }
    private function syncPayslip(User $user,Payslip $payslip):void {
        $data=['merchant_id'=>$payslip->merchant_id,'title'=>'Payslip - '.$payslip->pay_date?->format('M d, Y'),'reference'=>$payslip->reference,'document_date'=>$payslip->pay_date];
        $this->documentVaultRepository->firstOrCreateForSource($user->id,DocumentVaultDocument::TYPE_PAYSLIP,$payslip->uuid,$data);
        if($payslip->receipt)$this->documentVaultRepository->firstOrCreateForSource($user->id,DocumentVaultDocument::TYPE_RECEIPT,$payslip->receipt->uuid,['merchant_id'=>$payslip->merchant_id,'title'=>'Salary disbursement receipt','reference'=>$payslip->receipt->external_reference,'document_date'=>$payslip->receipt->occurred_at]);
    }
}
