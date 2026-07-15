# NOC Hub — Mobile API Documentation

**Version:** 1.0  
**Base URL:** `https://<hub-domain>/api/hub/mobile`  
**Authentication:** Bearer Token (Sanctum)  
**Content-Type:** `application/json`

---

## Overview

The NOC Hub aggregates data from multiple NOC (Network Operations Center) instances into a single API. The mobile app connects exclusively to the Hub — it never communicates directly with individual NOC instances.

Data is refreshed automatically from each NOC on a scheduled basis (every 30 seconds).

---

## Authentication

All endpoints except **Login** require an `Authorization` header:

```
Authorization: Bearer {token}
```

### 1. Login

```
POST /login
```

Authenticates the user and returns a Bearer token.

**Request body:**

```json
{
  "email": "user@example.com",
  "password": "your_password"
}
```

**Success response — 200 OK:**

```json
{
  "token": "1|abc123...",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "John",
    "last_name": "Doe",
    "username": "jdoe",
    "email": "user@example.com",
    "role": "Admin"
  }
}
```

**Error response — 422 Unprocessable Entity:**

```json
{
  "message": "The provided credentials are incorrect.",
  "errors": {
    "email": ["The provided credentials are incorrect."]
  }
}
```

> **Note:** Each login revokes any previously issued mobile token for that account. Only one active token per user.

---

### 2. Get Profile

```
GET /profile
```

Returns the authenticated user's info along with their assigned cinema locations.

**Headers:** `Authorization: Bearer {token}`

**Success response — 200 OK:**

```json
{
  "user": {
    "id": 1,
    "name": "John",
    "last_name": "Doe",
    "username": "jdoe",
    "email": "user@example.com",
    "role": "Admin"
  },
  "locations": [
    {
      "id": 3,
      "name": "BPJ Cinema",
      "city": "Kuala Lumpur",
      "country": "Malaysia",
      "noc_instance": {
        "id": 1,
        "name": "NOC Malaysia"
      }
    }
  ]
}
```

---

### 3. Logout

```
POST /logout
```

Revokes the current token.

**Headers:** `Authorization: Bearer {token}`

**Success response — 200 OK:**

```json
{
  "message": "Logged out successfully."
}
```

---

## Playback

### 4. List All Playbacks

```
GET /playback
```

Returns the real-time playback status for all screens across the user's assigned locations.

**Headers:** `Authorization: Bearer {token}`

**Success response — 200 OK:**

```json
{
  "playbacks": [
    {
      "id": 1,
      "noc_instance_id": 1,
      "location_id": 3,
      "screen_id": 7,
      "noc_name": "NOC Malaysia",
      "location_name": "BPJ Cinema",
      "screen_name": "Screen 1",
      "screen_model": "Barco",
      "playback_status": "Playing",
      "spl_title": "AVATAR 2",
      "cpl_title": "AVATAR-2_FTR_S_EN-XX_MY_51_2K_20220101_IOP_OV",
      "elapsed_runtime": "01:15:00",
      "remaining_runtime": "00:47:00",
      "progress_bar": 62,
      "projector_status": "On",
      "projector_lamp_stat": "OK",
      "lamp_status": "On",
      "dowser_status": "Open",
      "ip_management_server_status": "Connected",
      "storage_generale_status": "OK",
      "schedule_mode": "Auto",
      "securityManager": "Active",
      "soap_session": "Connected",
      "sound_model": "QSC",
      "ip_sound_status": "Connected",
      "mute_status": "Unmuted",
      "fader_status": "Full",
      "format_status": "3D",
      "bit_stream": "Active",
      "synced_at": "2026-06-05T10:30:00.000000Z"
    }
  ]
}
```

---

### 5. Playback Detail

```
GET /playback/{id}
```

Returns full details for a single screen playback.

**Path parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `id` | integer | Hub playback record ID |

**Headers:** `Authorization: Bearer {token}`

**Success response — 200 OK:**

```json
{
  "playback": {
    "id": 1,
    "playback_status": "Playing",
    "spl_title": "AVATAR 2",
    "..."  : "..."
  },
  "screen_info": {
    "id": 7,
    "screen_name": "Screen 1",
    "screen_number": 1,
    "screen_model": "Barco"
  },
  "location_info": {
    "id": 3,
    "name": "BPJ Cinema",
    "city": "Kuala Lumpur",
    "country": "Malaysia"
  },
  "noc": {
    "id": 1,
    "name": "NOC Malaysia"
  }
}
```

**Error — 403 Forbidden:** Returned if the playback belongs to a location the user is not assigned to.  
**Error — 404 Not Found:** Playback record does not exist.

---

## Schedules

All schedule endpoints accept an optional `date` query parameter (`YYYY-MM-DD`). Defaults to today.

### 6. List Schedules

```
GET /schedules?date=2026-06-05
```

Returns all schedules for the given date across the user's locations.

**Query parameters:**

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `date` | string | No | today | Date in `YYYY-MM-DD` format |

**Success response — 200 OK:**

```json
{
  "schedules": [
    {
      "id": 12,
      "noc_instance_id": 1,
      "location_id": 3,
      "screen_id": 7,
      "display_title": "AVATAR 2",
      "type": "Feature",
      "date_start": "2026-06-05 14:00:00",
      "date_end": "2026-06-05 16:15:00",
      "status": "linked",
      "cpls": 1,
      "kdm": 1,
      "kdm_notes": [
        { "status": "valid", "date": "2026-06-30 00:00:00" }
      ],
      "uuid_spl": "urn:uuid:abc123...",
      "location": { "id": 3, "name": "BPJ Cinema", "city": "Kuala Lumpur" },
      "screen": { "id": 7, "screen_name": "Screen 1", "screen_number": 1 }
    }
  ]
}
```

**`status` values:**

| Value | Meaning |
|-------|---------|
| `linked` | Schedule is linked to a CPL/movie |
| `unlinked` | Not linked |
| `manual` | Manually scheduled |

**`kdm` values:**

| Value | Meaning |
|-------|---------|
| `0` | No KDM |
| `1` | KDM valid |
| `2` | KDM expired |

---

### 7. Unlinked Schedules

```
GET /schedules/issues/unlinked?date=2026-06-05
```

Returns schedules that are not linked to a movie.

**Same query parameters and response format as List Schedules.**

---

### 8. Missing CPLs

```
GET /schedules/issues/missing-cpls?date=2026-06-05
```

Returns linked schedules that are missing their CPL (Content Package).

---

### 9. Missing KDMs

```
GET /schedules/issues/missing-kdms?date=2026-06-05
```

Returns linked schedules that have a CPL but are missing a KDM (Key Delivery Message).

---

### 10. Expired KDMs

```
GET /schedules/issues/kdm-expired?date=2026-06-05
```

Returns schedules where the KDM has already expired (`kdm = 2`).

---

### 11. Expiring KDMs

```
GET /schedules/issues/kdm-expiring
```

Returns schedules with KDMs that will expire soon.

**Query parameters:**

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `hours` | integer | No | 48 | Warn if KDM expires within this many hours |

---

## Errors

### 12. Error Summary

```
GET /errors/summary
```

Returns aggregated error counts across all of the user's locations.

**Success response — 200 OK:**

```json
{
  "kdm_errors": 3,
  "nbr_sound_alert": 1,
  "nbr_projector_alert": 2,
  "nbr_server_alert": 0,
  "nbr_storage_errors": 1,
  "nbr_tms_alert": 2,
  "total_errors": 9
}
```

| Field | Description |
|-------|-------------|
| `kdm_errors` | Total KDM errors |
| `nbr_sound_alert` | Sound system alerts |
| `nbr_projector_alert` | Projector alerts |
| `nbr_server_alert` | Server alerts |
| `nbr_storage_errors` | Storage errors |
| `nbr_tms_alert` | TMS alerts |
| `total_errors` | Sum of all above |

---

### 13. KDM Errors

```
GET /errors/kdm
```

Returns the list of KDM errors.

**Success response — 200 OK:**

```json
{
  "kdms_errors_list": [
    {
      "id": 1,
      "location_id": 3,
      "noc_instance_id": 1,
      "cpl_id": "urn:uuid:cpl-abc123",
      "annotationText": "AVATAR 2 - KDM Expired",
      "details": "KDM expired on 2026-05-01",
      "serverName": "Server-01",
      "date_time": "2026-06-05 08:00:00",
      "location": { "id": 3, "name": "BPJ Cinema" },
      "noc_instance": { "id": 1, "name": "NOC Malaysia" }
    }
  ]
}
```

---

### 14. Server Errors

```
GET /errors/server
```

**Success response — 200 OK:**

```json
{
  "server_errors_list": [
    {
      "id": 1,
      "location_id": 3,
      "noc_instance_id": 1,
      "event_id": "EVT-001",
      "date": "2026-06-05 09:15:00",
      "class": "Hardware",
      "type": "Disk",
      "sub_type": "ReadError",
      "criticity": "High",
      "error_code": "0x1A2B",
      "server_name": "TMS-Server-01",
      "message": "Disk read error on sector 0x1A2B",
      "recommended_action": "Replace the faulty disk.",
      "ip_projector": "192.168.1.10",
      "projector_brand": "Barco",
      "projector_ip": "192.168.1.11",
      "projector_model": "DP2K-10S",
      "sound_brand": "QSC",
      "screen_model": "Screen 1",
      "display_message": "Disk fault detected on primary storage array.",
      "certificat_date": "2026-12-31",
      "serial_number": "SN-123456",
      "show_title": "The Movie Title",
      "product_name": "IMS3000",
      "synced_at": "2026-07-07T08:00:00.000000Z",
      "location": { "id": 3, "name": "BPJ Cinema" },
      "noc_instance": { "id": 1, "name": "NOC Malaysia" }
    }
  ]
}
```

---

### 15. Projector Errors

```
GET /errors/projector
```

**Success response — 200 OK:**

```json
{
  "projector_errors_list": [
    {
      "id": 1,
      "location_id": 3,
      "noc_instance_id": 1,
      "title": "Lamp Error",
      "time_saved": "2026-06-05 10:00:00",
      "code": "E-042",
      "severity": "Warning",
      "message": "Lamp hours exceeded threshold",
      "recommended_action": "Schedule lamp replacement within 48h.",
      "server_name": "Projector-Screen1",
      "projector_brand": "Barco",
      "projector_model": "DP2K-10S",
      "display_message": "Lamp runtime has exceeded the recommended threshold.",
      "synced_at": "2026-07-07T08:00:00.000000Z",
      "location": { "id": 3, "name": "BPJ Cinema" },
      "noc_instance": { "id": 1, "name": "NOC Malaysia" }
    }
  ]
}
```

---

### 16. Sound Errors

```
GET /errors/sound
```

**Success response — 200 OK:**

```json
{
  "sounds_errors_list": [
    {
      "id": 1,
      "location_id": 3,
      "noc_instance_id": 1,
      "alarm_id": "ALM-005",
      "date_saved": "2026-06-05 07:45:00",
      "severity": "Critical",
      "title": "Amplifier Fault",
      "clearable": true,
      "hardware": "QSC Amp-1",
      "screen": "Screen 2",
      "message": "Amplifier channel 1 fault detected.",
      "recommended_action": "Check amplifier power supply and cable connections.",
      "device_sub_type_model": "CX-602",
      "device_sub_type_title": "Crown Amplifier",
      "sound_ip": "192.168.1.20",
      "display_message": "Critical fault on amplifier unit CX-602.",
      "synced_at": "2026-07-07T08:00:00.000000Z",
      "location": { "id": 3, "name": "BPJ Cinema" },
      "noc_instance": { "id": 1, "name": "NOC Malaysia" }
    }
  ]
}
```

---

### 17. Storage Errors

```
GET /errors/storage
```

**Success response — 200 OK:**

```json
{
  "storage_errors_list": [
    {
      "id": 1,
      "location_id": 3,
      "noc_instance_id": 1,
      "server_name": "NAS-Server-01",
      "message": "RAID array degraded — one disk failed.",
      "recommended_action": "Replace failed disk immediately.",
      "storage_generale_status": "critical",
      "projector_brand": "Barco",
      "projector_ip": "192.168.1.11",
      "projector_model": "DP2K-10S",
      "sound_brand": "QSC",
      "screen_model": "Screen 1",
      "display_message": "RAID array in degraded state. Immediate attention required.",
      "synced_at": "2026-07-07T08:00:00.000000Z",
      "location": { "id": 3, "name": "BPJ Cinema" },
      "noc_instance": { "id": 1, "name": "NOC Malaysia" }
    }
  ]
}
```

---

### 18. TMS Errors

```
GET /errors/tms
```

Returns the list of TMS (Theatre Management System) errors.

**Success response — 200 OK:**

```json
{
  "tms_errors_list": [
    {
      "id": 1,
      "location_id": 3,
      "noc_instance_id": 1,
      "id_tms_error": "TMS-ERR-001",
      "title": "Network timeout",
      "code": "NET-001",
      "severity": "high",
      "message": "Connection to projector timed out.",
      "time_saved": "2026-07-07 08:00:00",
      "id_screen": 7,
      "ip_projector": "192.168.1.10",
      "recommended_action": "Check network cable and projector power.",
      "display_message": "Projector at 192.168.1.10 is unreachable.",
      "device_sub_type": "projector",
      "device_sub_type_ip": "192.168.1.11",
      "device_sub_type_model": "DP2K-10S",
      "device_sub_type_title": "Barco Projector",
      "server_name": "Screen 1",
      "screen_model": "DP2K",
      "projector_ip": "192.168.1.11",
      "projector_brand": "Barco",
      "projector_model": "DP2K-10S",
      "sound_ip": "192.168.1.20",
      "sound_brand": "QSC",
      "number": 1,
      "session_start": "2026-07-13 12:00:00",
      "spl_title": "MALAM 3 YASINAN",
      "movie_title": "Malam 3 Yasinan",
      "synced_at": "2026-07-07T08:00:00.000000Z",
      "location": { "id": 3, "name": "BPJ Cinema" },
      "noc_instance": { "id": 1, "name": "NOC Malaysia" }
    }
  ]
}
```

`session_start`, `spl_title` and `movie_title` are only populated when `device_sub_type` is `"playback"` — they are `null` for other sub-types (e.g. `server`, `raid`).

---

## Error Responses

| HTTP Status | Meaning |
|-------------|---------|
| `200` | Success |
| `401` | Unauthorized — missing or invalid token |
| `403` | Forbidden — resource belongs to a location the user cannot access |
| `404` | Not Found |
| `422` | Validation error (check `errors` field in response) |
| `500` | Server error |

**Unauthorized (401) example:**

```json
{
  "message": "Unauthenticated."
}
```

---

## Quick Reference

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/login` | No | Login and get token |
| GET | `/profile` | Yes | Authenticated user info |
| POST | `/logout` | Yes | Revoke token |
| GET | `/playback` | Yes | All screens playback status |
| GET | `/playback/{id}` | Yes | Single screen detail |
| GET | `/schedules` | Yes | Schedules for a date |
| GET | `/schedules/issues/unlinked` | Yes | Unlinked schedules |
| GET | `/schedules/issues/missing-cpls` | Yes | Schedules missing CPLs |
| GET | `/schedules/issues/missing-kdms` | Yes | Schedules missing KDMs |
| GET | `/schedules/issues/kdm-expired` | Yes | Expired KDM schedules |
| GET | `/schedules/issues/kdm-expiring` | Yes | KDMs expiring soon |
| GET | `/errors/summary` | Yes | Aggregated error counts |
| GET | `/errors/kdm` | Yes | KDM errors list |
| GET | `/errors/server` | Yes | Server errors list |
| GET | `/errors/projector` | Yes | Projector errors list |
| GET | `/errors/sound` | Yes | Sound errors list |
| GET | `/errors/storage` | Yes | Storage errors list |
| GET | `/errors/tms` | Yes | TMS errors list |

---

## Implementation Notes for Mobile Developer

1. **Token storage:** Store the token securely (iOS Keychain / Android Keystore). Send it on every request as `Authorization: Bearer {token}`.

2. **Data scope:** All responses are automatically filtered to the logged-in user's assigned locations. The app does not need to pass location filters — the API handles this server-side.

3. **Data freshness:** The hub syncs with NOC instances every ~30 seconds. The `synced_at` field on playback records indicates when data was last updated from the NOC.

4. **Polling:** For real-time playback monitoring, poll `GET /playback` every 30–60 seconds.

5. **Schedules:** Always pass `date` in `YYYY-MM-DD` format. Without it, defaults to today (server timezone).

6. **KDM expiry warning threshold:** The `/schedules/issues/kdm-expiring` endpoint accepts a `?hours=48` parameter (default 48 hours). Adjust as needed.

7. **Error handling:** Always check the HTTP status code first. A `401` means the token expired or was revoked — redirect to the login screen.
