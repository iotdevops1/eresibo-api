# PusoPay receipt lookup

Authenticate with the same `X-API-Key` header as receipt creation. The key must
have the explicit `receipts.read` scope. Both lookups return one receipt:

```http
GET /api/v1/integrations/pusopay/receipts?externalReference=PUSOPAY-123
GET /api/v1/integrations/pusopay/receipts?receiptId=<receipt-uuid>
X-API-Key: <integration-key>
Accept: application/json
```

Supply exactly one identifier. URL-encode external references, especially
references containing `+`, `&`, or `#`. The response uses the same identifiers,
URL, status object, and expiry as creation, plus the transaction details:

```json
{
  "success": true,
  "message": "Receipt retrieved successfully.",
  "data": {
    "receiptId": "550e8400-e29b-41d4-a716-446655440000",
    "externalReference": "PUSOPAY-123",
    "receiptUrl": "https://portal.example/r/<public-token>",
    "status": { "id": 1, "name": "CONFIRMED" },
    "expiresAt": "2026-12-23T00:00:00.000000Z",
    "amountMinor": 100000,
    "currency": "PHP",
    "transactionType": "MERCHANT_PAYMENT",
    "counterpartyLabel": "Example merchant",
    "occurredAt": "2026-09-24T00:00:00.000000Z",
    "isExpired": false
  }
}
```

- `200`: existing PusoPay receipt, including expired or failed receipts.
- `401`: missing, invalid, inactive, or expired key.
- `403`: key lacks `receipts.read`.
- `404`: no matching PusoPay receipt, including soft-deleted receipts.
- `422`: missing identifiers, both identifiers supplied, invalid UUID, or
  external reference longer than 100 characters.

This is an authenticated recovery lookup. It does not create receipts, move
funds, send webhooks, change status, or renew the public URL. An expired receipt
is returned with `isExpired: true`; its public URL retains its original expiry.
Authentication updates the integration key's last-used timestamp as usual.

Access is to the PUSOPAY source as a whole. Receipts are not currently associated
with individual keys or merchants. Multiple keys with `receipts.read` share this
PusoPay receipt namespace, including rotated keys. ERESIBO/payroll receipts are
excluded. This is not a per-merchant isolation mechanism.

## Deployment and scopes

Apply the migration before serving the updated API:

```bash
docker exec -it eresibo_api_app php artisan migrate
```

Existing keys retain their former write access (`receipts.create` and
`funding.confirm`), but are not automatically granted receipt reads. To make an
existing key receipts-only, use its UUID from `integration_api_keys.uuid`:

```bash
docker exec -it eresibo_api_app php artisan integration:set-key-scopes KEY_UUID --scope=receipts.create --scope=receipts.read
```

This command REPLACES the key's scope list. Include `--scope=funding.confirm` only
for a key intended to retain funding access. To provision a new read-only key:

```bash
docker exec -it eresibo_api_app php artisan integration:generate-api-key PusoPay-read --environment=sandbox --scope=receipts.read
```

New keys require explicit `--scope` options. No scope or a wildcard grants no
access. Receipt creation requires `receipts.create`; funding confirmation
requires `funding.confirm`. A receipts-only key receives `403` on funding.
The internal portal lookup keeps its separate authentication.
