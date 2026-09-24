<?php
namespace App\Http\Controllers\Api\Admin;
use App\Http\Controllers\BaseApiController;
use App\Http\Requests\DocumentVault\DocumentVaultFilterRequest;
use App\Http\Requests\DocumentVault\UpdateDocumentVaultDocumentRequest;
use App\Http\Resources\DocumentVaultCollection;
use App\Http\Resources\DocumentVaultDocumentResource;
use App\Services\DocumentVault\DocumentVaultService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class DocumentVaultController extends BaseApiController {
    public function __construct(protected DocumentVaultService $documentVaultService){}
    public function index(DocumentVaultFilterRequest $request):JsonResponse {
        $result=$this->documentVaultService->administratorIndex($request->user(),$request->validated());$collection=new DocumentVaultCollection($result['documents']);$collection->counts=$result['counts'];
        return $this->success($collection,'Administrator document vault retrieved successfully.');
    }
    public function show(Request $request,string $uuid):JsonResponse{return $this->success(new DocumentVaultDocumentResource($this->documentVaultService->administratorShow($request->user(),$uuid)),'Document retrieved successfully.');}
    public function update(UpdateDocumentVaultDocumentRequest $request,string $uuid):JsonResponse{return $this->success(new DocumentVaultDocumentResource($this->documentVaultService->administratorSetArchived($request->user(),$uuid,$request->boolean('archived'))),'Document updated successfully.');}
}
