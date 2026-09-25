# Public document verification

No login or integration API key is needed to verify an existing registered document.

## Portal

GET /verify?document=PAYSLIP-<payslip-uuid>

The same form accepts receipt UUIDs, public receipt tokens, and exact integration external references. URL-encode the reference when constructing links (including +, &, /, # and spaces).

Example:

http://localhost:8080/verify?document=PAYSLIP-c3cabfd5-be33-4558-8e6b-b2a730e11a71

## Public API

GET /api/v1/public/documents/verify?document=<reference>

Responses:

- 200: state=verified, success=true, data contains public document metadata.
- 404: state=not_found, data=null. Also used for failed/deleted receipts and ambiguous matches.
- 410: state=expired, data=null. Receipt publication expiry is respected.
- 422: invalid or missing input (required string, maximum 100 characters).
- 429: request limit exceeded.

The API limit is 60 requests per minute per client IP. The portal limit is 30. Results use no-store caching and noindex headers.

Payslip references report issuance and acknowledgement status independently of receipt publication expiry. Pending acknowledgement does not mean payment is complete. Public output includes the net amount and issuer, but excludes employee identities, contact details, wallet IDs, payroll line items and deductions.

Receipts from any connected system are supported once registered in the existing receipts table. This does not connect to arbitrary external payment systems or authenticate uploaded images/PDFs. Receipt creation remains protected by the integration API key and its receipt-creation scope.

## Calling the API

Use GET with the `document` query parameter; do not send a request body, bearer token, or integration key.

```bash
curl --get "https://YOUR_API_HOST/api/v1/public/documents/verify" \
  --header "Accept: application/json" \
  --data-urlencode "document=PAYSLIP-c3cabfd5-be33-4558-8e6b-b2a730e11a71"
```

Supported identifiers are `PAYSLIP-<uuid>`, a receipt UUID, a receipt public token, or an exact receipt external reference. A bare payslip UUID is not supported. Verification is read-only: it does not acknowledge a payslip, credit a wallet, or create a receipt.

## Response format

The endpoint uses the standard API `success` and `message` fields, with public metadata in `data`. The existing top-level `state` field is retained for portal compatibility.

Example HTTP 200 for a pending payslip:

```json
{
  "success": true,
  "message": "Document verified successfully.",
  "data": {
    "document_type": "Payslip",
    "reference": "PAYSLIP-c3cabfd5-be33-4558-8e6b-b2a730e11a71",
    "source_system": "ERESIBO",
    "issuer": "Example Employer",
    "status": "Pending acknowledgement",
    "issued_at": "2026-09-17T08:00:00.000000Z",
    "pay_date": "2026-09-17",
    "amount_minor": 15000,
    "currency": "PHP",
    "acknowledged_at": null
  },
  "state": "verified"
}
```

`amount_minor` is in the currency's minor units (15000 means PHP 150.00). A verified document confirms that a supported record exists and meets the verification rules; a pending payslip does not confirm receipt of funds.

Receipt data contains `document_type`, `reference`, `receipt_id`, `source_system`, `status`, `issued_at`, `amount_minor`, `currency`, `transaction_type`, and `expires_at`. Only confirmed, unexpired receipts are verified.

Example HTTP 404:

```json
{
  "success": false,
  "message": "Document not found.",
  "errors": null,
  "state": "not_found",
  "data": null
}
```

HTTP 410 uses the same structure with `state: "expired"` and `message: "Document verification link has expired."`. HTTP 422 and 429 use the application's standard error handler; they do not have a verification `state`. Validation errors include `errors.document`.

## Backend structure

The route remains public and invokable with `throttle:60,1`. The implementation follows the other feature APIs:

- `VerifyDocumentRequest`: validates the required reference.
- `VerifyDocumentController`: extends `BaseApiController`, delegates verification, and returns the standard response plus compatibility fields and privacy headers.
- `DocumentVerificationService`: applies payslip status and receipt expiry/confirmation rules.
- `DocumentVerificationRepository`: performs read-only lookups and rejects ambiguous receipt matches.
- `VerifiedDocumentResource`: explicitly lists the fields safe to return publicly.

Deploy these API files together. Existing portal callers can keep using `state` and `data`. No database migration, permission seed, or API key scope change is required for this refactor.
