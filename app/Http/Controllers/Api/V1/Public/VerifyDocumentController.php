<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\DocumentVerification\VerifyDocumentRequest;
use App\Http\Resources\VerifiedDocumentResource;
use App\Services\DocumentVerification\DocumentVerificationService;
use Illuminate\Http\JsonResponse;

class VerifyDocumentController extends BaseApiController
{
    public function __construct(protected DocumentVerificationService $documentVerificationService) {}

    public function __invoke(VerifyDocumentRequest $request): JsonResponse
    {
        $result = $this->documentVerificationService->verify($request->validated('document'));

        $response = match ($result['state']) {
            'verified' => $this->success(
                new VerifiedDocumentResource($result['document']),
                'Document verified successfully.',
            ),
            'expired' => $this->error('Document verification link has expired.', status: 410),
            default => $this->error('Document not found.', status: 404),
        };

        // Retain the verification contract alongside the standard API envelope.
        $payload = $response->getData(true);
        $payload['state'] = $result['state'];
        $payload['data'] ??= null;

        return $response->setData($payload)
            ->header('Cache-Control', 'no-store, private')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
