# eResibo — PusoPay Receipt Lookup API
## Client integration guide

Version: 1.0  
Updated: 25 September 2026  
Audience: PusoPay developers and integration testers

## 1. What this API does

Retrieve an existing eResibo receipt using either your PusoPay transaction
reference or the receipt ID returned by eResibo. This provides a recovery path
when your system needs the receipt URL, lost the original creation response, or
wants to compare stored receipt details.

This GET request does not create another receipt, transfer money, change the
receipt, renew its public link, or trigger a receipt webhook. Receipt status
describes the eResibo record; it is not an independent verification of settlement
in PusoPay or a bank.

**Suggested explanation to your client:**

> Your system can retrieve a receipt using the same integration API key used for
> receipt creation, provided that key has receipt-read permission. Send either
> your transaction reference or the eResibo receipt ID. The API returns the saved
> receipt URL, status, expiry, and transaction details.

## 2. Before you start

Obtain these from the eResibo integration team:

| Item | Description |
| --- | --- |
| Base URL | The address for the environment you are testing, without a trailing slash or an extra `/api`. |
| Integration API key | The secret value to send in the `X-API-Key` header. |
| Read permission | The key must be active, unexpired, and granted the `receipts.read` scope. |
| Existing receipt identifier | Either the original `externalReference` or the returned `receiptId`. |

Use the host and key supplied for the intended sandbox or production deployment.
The examples use a placeholder host; replace it with the actual address.

Keep the integration key in your server configuration or local secret store.
Do not include the key in a public frontend, request URL, or shared collection
export.

### Which identifier should you use?

| Value | Purpose | Use as a lookup query? |
| --- | --- | --- |
| `externalReference` | Your reference sent when creating the receipt. | Yes |
| `receiptId` | eResibo receipt UUID returned in `data.receiptId`. | Yes |
| Integration-key UUID | Identifies the API-key record for administration. | No |
| API secret | Authenticates the request through `X-API-Key`. | No |
| Public token | Part of the receipt's public URL. | No |

If your creation response was lost, use `externalReference`; you do not need to
know the eResibo receipt ID first.

## 3. Endpoint and authentication

```http
GET /api/v1/integrations/pusopay/receipts
X-API-Key: YOUR_INTEGRATION_API_KEY
Accept: application/json
```

Use an API key in the header, not a portal-login Bearer token. There is no request
body. Both lookup methods require `receipts.read`.

### Query parameters

| Parameter | Format | When to supply |
| --- | --- | --- |
| `externalReference` | Non-empty string; maximum 100 characters. | When looking up your original transaction reference. |
| `receiptId` | Valid receipt UUID. | When looking up the ID returned by eResibo. |

**Supply exactly one parameter per request.** Omitting both or supplying both
with values returns `422`. Parameter names are case-sensitive: use
`externalReference` and `receiptId` exactly as written.

This endpoint returns one matching receipt; it is not a receipt-list endpoint.
Use URL encoding for references containing spaces, `+`, `&`, `#`, or other
special characters. Prefer your HTTP client's query-parameter encoder over
manual string concatenation.

## 4. Request examples

### By external reference

```http
GET https://api.example.com/api/v1/integrations/pusopay/receipts?externalReference=PUSOPAY-20260925-001
X-API-Key: YOUR_INTEGRATION_API_KEY
Accept: application/json
```

### By eResibo receipt ID

```http
GET https://api.example.com/api/v1/integrations/pusopay/receipts?receiptId=550e8400-e29b-41d4-a716-446655440000
X-API-Key: YOUR_INTEGRATION_API_KEY
Accept: application/json
```

The UUID above is illustrative. Replace it with a real `data.receiptId` from your
receipt creation or lookup response.

### cURL (Bash / Linux / WSL)

Set `ERESIBO_BASE_URL` and `ERESIBO_API_KEY` in your environment before running
these examples. The base URL must not end in `/api` or `/`.

By external reference:

```bash
curl --get "$ERESIBO_BASE_URL/api/v1/integrations/pusopay/receipts" \
  --header "X-API-Key: $ERESIBO_API_KEY" \
  --header "Accept: application/json" \
  --data-urlencode "externalReference=PUSOPAY-20260925-001"
```

By receipt ID:

```bash
curl --get "$ERESIBO_BASE_URL/api/v1/integrations/pusopay/receipts" \
  --header "X-API-Key: $ERESIBO_API_KEY" \
  --header "Accept: application/json" \
  --data-urlencode "receiptId=550e8400-e29b-41d4-a716-446655440000"
```

These commands encode the query value automatically.

## 5. Testing in Postman

Import the accompanying `pusopay-receipt-lookup.postman_collection.json`.
Configure these variables locally:

| Variable | Value |
| --- | --- |
| `baseUrl` | The provided environment's base URL. |
| `apiKey` | Your existing integration API secret with `receipts.read`. |
| `externalReference` | A reference for an existing receipt. |
| `receiptId` | A real eResibo receipt UUID, when using ID lookup. |

The collection contains two GET requests, one for each lookup method. Open the
appropriate request and send it. Authentication is already configured as
`X-API-Key: {{apiKey}}`; no request body is required. The collection contains no
real credentials.

For a manual request:

1. Select `GET`.
2. Enter `{{baseUrl}}/api/v1/integrations/pusopay/receipts`.
3. Add either `externalReference` or `receiptId` as a query parameter.
4. Add `X-API-Key: {{apiKey}}` and `Accept: application/json` as headers.
5. Send the request and check the HTTP status and response body.

If your test has no receipt yet, use the existing
`POST /api/v1/integrations/pusopay/receipts` creation flow, which requires
`receipts.create`. Save its returned `data.receiptId`, then run the GET lookup.

## 6. Successful response

HTTP status: `200 OK`

Illustrative response:

```json
{
  "success": true,
  "message": "Receipt retrieved successfully.",
  "data": {
    "receiptId": "550e8400-e29b-41d4-a716-446655440000",
    "externalReference": "PUSOPAY-20260925-001",
    "receiptUrl": "https://receipts.example.com/r/xj0O0MdDYnnqsuiZI6BSVw",
    "status": {
      "id": 1,
      "name": "CONFIRMED"
    },
    "expiresAt": "2026-12-24T02:30:00.000000Z",
    "amountMinor": 100000,
    "currency": "PHP",
    "transactionType": "MERCHANT_PAYMENT",
    "counterpartyLabel": "Example merchant",
    "occurredAt": "2026-09-25T02:30:00.000000Z",
    "isExpired": false
  }
}
```

### Response fields

| Field | Meaning |
| --- | --- |
| `success` | Whether the lookup request succeeded. |
| `message` | Human-readable request result. |
| `data.receiptId` | eResibo receipt UUID. |
| `data.externalReference` | The stored PusoPay reference. |
| `data.receiptUrl` | Saved public receipt URL. |
| `data.status` | Receipt status object: `1 / CONFIRMED`, `2 / FAILED`; unknown stored values return `UNKNOWN`. |
| `data.expiresAt` | Public-link expiry timestamp. |
| `data.amountMinor` | Integer amount in currency minor units. For PHP, `100000` means PHP 1,000.00. |
| `data.currency` | Stored currency code, for example `PHP`. |
| `data.transactionType` | Stored transaction category, such as `TRANSFER` or `MERCHANT_PAYMENT`. |
| `data.counterpartyLabel` | Stored counterparty name or label; may be `null`. |
| `data.occurredAt` | Stored transaction date/time, rather than the lookup time. |
| `data.isExpired` | Whether the public receipt link has expired. |

Timestamps are ISO 8601 strings. A trailing `Z` indicates UTC; convert for local
display if needed.

A `200` response means the record was found. Your application should also check
`data.status.name` and `data.isExpired` before presenting it as a confirmed,
currently accessible receipt.

## 7. Expired receipts and recovery

The authenticated lookup can retrieve an expired or failed receipt for
reconciliation. An expired receipt returns `200` with `isExpired: true` and the
original `expiresAt`.

Retrieval does not extend the public link's expiry or change `CONFIRMED` to a
different receipt status. Receipt status and link expiry are separate values.
This endpoint does not provide a renewal action.

If creation times out, retain your original `externalReference` and use it to
look up the receipt. If you receive `404` immediately after the timeout, the
creation may still be in progress; retry the GET a few times with increasing
delays. A persistent `404` needs investigation against the original creation
attempt. Do not treat a lookup failure as proof that a payment failed.

## 8. Errors and troubleshooting

| HTTP status | Meaning | Client action |
| --- | --- | --- |
| `401` | Missing, invalid, inactive, or expired API key. | Check the header, key, and environment with eResibo. |
| `403` | Valid key lacks `receipts.read`. | Ask eResibo to grant the required scope. |
| `404` | No matching accessible receipt. | Check the identifier and target environment. Deleted and non-PusoPay receipts also return this status. |
| `422` | Lookup parameters failed validation. | Use exactly one valid identifier; inspect `errors`. |
| `500` | Unexpected server error. | Retry the GET with bounded delays; report persistent failures to eResibo. |

Example missing-scope response:

```json
{
  "success": false,
  "message": "API key does not have the required scope.",
  "requiredScope": "receipts.read"
}
```

Validation responses contain `success: false`, a `message`, and an `errors`
object keyed by the invalid parameter. Authentication and not-found responses
contain `success: false` and a `message`. Use the HTTP status and structured
fields in your application; human-readable wording may vary.

When reporting a problem, include the HTTP status, response body, request time,
environment, and receipt identifier. Omit the API secret.

## 9. Access and integration boundaries

The lookup retrieves receipts stored under the PUSOPAY source. Other eResibo
documents, including payroll receipts under another source, are excluded.
Deleted receipts are not returned.

The current access model is shared across PusoPay integration keys with
`receipts.read`; it does not isolate receipt visibility per key, merchant, or
employee. The integration must be used by the trusted PusoPay backend.

A receipts-only key normally has `receipts.create` and `receipts.read`. Funding
requires its own `funding.confirm` scope. The separate
`/api/v1/internal/receipts/{publicToken}` endpoint is for the internal portal and
uses different authentication.

No webhook is required to call this GET endpoint. Existing webhook behavior on
receipt creation is a separate integration concern.

## 10. Client acceptance checklist

- Retrieve the same test receipt by its external reference and receipt ID.
- Confirm both methods return the same `receiptId`, amount, URL, status, and expiry.
- Confirm an unknown identifier returns `404`.
- Confirm invalid or missing query parameters return `422`.
- Confirm a key without `receipts.read` returns `403`.
- Confirm an expired test receipt remains recoverable with `isExpired: true`.
- Store `externalReference` and `receiptId` together for future recovery.

The implementation has local automated coverage for these lookup behaviors,
authorization, and absence of receipt mutation. Run the acceptance checks against
your supplied sandbox environment before production integration.
