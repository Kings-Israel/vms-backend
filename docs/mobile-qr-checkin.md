# Mobile: QR-Based Visitor Check-In

## Background

Security officers currently check in a pre-registered visitor by typing their
national ID or vehicle plate into the app, which calls `GET /v1/lookup/national-id`
or `GET /v1/lookup/plate`, then confirms via `POST /v1/visits/check-in`.

Tenants and admins can now pre-register a visitor from the web app, which
generates a one-time QR code shown on screen (and, if the tenant/admin shows it
to the visitor, on the visitor's own phone or a printout). This spec adds a
third lookup path — scan instead of type — reusing the same confirm/check-in
step already implemented.

The QR code encodes **only an opaque token string** (e.g.
`aB3xQ...`, 40 chars) — no personal data, no visit ID, nothing guessable.

## New endpoint

```
GET /v1/lookup/qr?token={scanned_string}
Authorization: Bearer {sanctum token}       (same auth as every other /v1 route)
```

**Response — token matches an active, unarrived visit:**
```json
{
  "found": true,
  "blacklisted": false,
  "visit": {
    "id": 123,
    "status": "expected",
    "expected_arrival": "2026-07-13T14:00:00Z",
    "purpose": "Meeting",
    "visitor": { "id": 45, "first_name": "...", "last_name": "...", "phone": "...", "company": "...", "is_blacklisted": false },
    "unit": { "id": 7, "name": "..." },
    "host": { "id": 12, "name": "..." },
    "vehicle": { "id": 3, "plate_number": "...", "make": "...", "model": "...", "color": "..." },
    "visitor_type": { "id": 2, "name": "...", "color": "#..." }
  }
}
```

**Response — visitor is blacklisted** (do not show visit details, do not allow check-in):
```json
{ "found": true, "blacklisted": true, "visit": null }
```

**Response — token unknown, expired, or already used** (all three collapse to
the same shape — a stale/reused QR is indistinguishable from a fake one, by
design):
```json
{ "found": false, "visit": null }
```

A token stops resolving the moment the underlying visit leaves `expected`
status — i.e. right after check-in, or if the tenant/admin cancels it. There is
no separate "expired" error code; treat every `found: false` the same way.

## UX flow

1. **Entry point**: add a "Scan QR" action next to the existing manual lookup
   flow (wherever national-ID/plate lookup lives today — likely the same
   check-in screen, as a third tab/button).
2. **Scanner**: open the device camera in QR-only mode (no need to decode
   barcodes/other formats). Include:
   - A torch/flashlight toggle (gatehouses are often dim).
   - A manual "enter code" text fallback for damaged/unscannable codes or
     camera failures — submits to the same `/v1/lookup/qr` endpoint.
   - Debounce: stop scanning and show a loading state the instant a QR is
     decoded, so the same code isn't submitted multiple times per second while
     the frame is still in view.
3. **On decode**, call `GET /v1/lookup/qr?token=...`:
   - `found: true, blacklisted: false` → show a confirmation card: visitor
     name/photo if available, unit, host, expected time, vehicle (if any),
     visitor type badge. Officer taps **Confirm Check-In**.
   - `found: true, blacklisted: true` → show a hard red blocking screen,
     "Entry Denied — visitor is blacklisted." No check-in action available.
     (Mirrors the existing blacklist block already implemented for the
     manual national-ID/walk-in paths.)
   - `found: false` → "Invalid or expired code." Offer to retry the scan or
     fall back to manual national-ID lookup / walk-in registration — don't
     dead-end the officer.
4. **Confirm**: tapping "Confirm Check-In" calls the existing
   `POST /v1/visits/check-in` with `{ "visit_id": <id from lookup> }` — no new
   check-in endpoint needed, this is the same call the manual-lookup flow
   already makes for a pre-registered visit.
5. **Result**: existing success/error handling applies (a 403 with
   `blacklisted: true` is still possible here in a race — e.g. someone
   blacklists the visitor in the few seconds between scan and confirm — so keep
   that check on the confirm step too, don't assume the scan-time blacklist
   check is still valid).

## Edge cases / error handling

| Case | Handling |
|---|---|
| No camera permission | Prompt to grant permission; if denied, fall back to manual code entry / existing lookup tabs — never a dead screen. |
| Token scanned twice in a row (already checked in) | Second scan returns `found: false` — show "Invalid or expired code," not a crash or stale confirmation card. |
| Poor lighting / camera can't focus | Torch toggle (above) + manual entry fallback. |
| No network at point of scan | Standard offline error banner, consistent with how the app already handles `todayExpected`/`checkIn` network failures. |
| Visitor blacklisted after scan but before confirm | `POST /v1/visits/check-in` still returns `403 { blacklisted: true }` in this case — handle it the same way the manual flow already does, don't rely solely on the scan-time check. |

## Explicitly out of scope for this change

- Generating or displaying the QR code on mobile — it's only ever generated
  and shown on the **web** side (tenant portal / admin visit registration).
  Mobile only **scans**.
- Any new auth/role surface — this uses the same `security_officer`-only
  Sanctum session the app already has.

## Acceptance checklist

- [ ] Scan a valid pre-registered visitor's QR → confirmation card shows
      correct visitor/unit/host/vehicle details.
- [ ] Confirm check-in from the scan flow → visit status flips to
      `checked_in`, matches manual check-in behavior (host gets notified, etc.).
- [ ] Re-scanning the same (now-consumed) QR → "Invalid or expired code."
- [ ] Scan a blacklisted visitor's QR → blocking screen, no check-in possible.
- [ ] Deny camera permission → manual code entry still works end-to-end.
- [ ] Airplane mode during scan → graceful offline error, no crash.
