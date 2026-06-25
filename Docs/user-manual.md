# YAAMS User Manual

**Yet Another Airline Management System** — a virtual airline management platform for flight simulation communities.

---

## Table of Contents

1. [First-Time Setup (Admin)](#1-first-time-setup-admin)
2. [Registering an Account](#2-registering-an-account)
3. [The Airline Portal](#3-the-airline-portal)
4. [Joining an Airline via Invite Code](#4-joining-an-airline-via-invite-code)
5. [Founding a New Airline](#5-founding-a-new-airline)
6. [Switching Between Airlines](#6-switching-between-airlines)
7. [The Dashboard](#7-the-dashboard)
8. [Filing a PIREP](#8-filing-a-pirep)
9. [Viewing Your Flights](#9-viewing-your-flights)
10. [Manager: Reviewing PIREPs](#10-manager-reviewing-pireps)
11. [Manager: Invite Codes](#11-manager-invite-codes)
12. [Manager: Fleet Management](#12-manager-fleet-management)
13. [Notifications](#13-notifications)
14. [Roles & Permissions](#14-roles--permissions)

---

## 1. First-Time Setup (Admin)

When YAAMS is installed for the first time and no users exist yet, navigating to the instance URL will redirect to the **Setup Wizard** at `/setup`.

The wizard collects everything needed to get the instance running in one step:

### Instance Settings
| Field | Description |
|---|---|
| Instance name | Displayed in the page title and header across the app (e.g. *FlySimWorld Virtual Airlines*) |

### Instance Policies
| Field | Description |
|---|---|
| Who can found new airlines? | **Admins only** — only the Super-Admin can create new airlines. **Any registered user** — any logged-in user can found their own airline. Super-Admins can always create airlines regardless of this setting. |

### Your First Airline
| Field | Description |
|---|---|
| Airline name | Full name of the airline (e.g. *Lufthansa Virtual*) |
| IATA prefix | 2-letter code used for flight numbers (e.g. `LH`) |
| ICAO callsign | 3-letter code used for ATC callsigns and invite codes (e.g. `DLH`) |
| ATC callsign | Voice callsign, letters only (e.g. `SPEEDBIRD`) |
| Main hub | ICAO code of the airline's primary hub airport (e.g. `EDDF`) |
| Country | ISO 3166-1 two-letter country code (e.g. `DE`) |
| Founded | Date the virtual airline was founded (optional) |
| Website | Public website URL (optional) |
| Description | Short description shown on the airline page (optional) |
| Weight unit | Whether the airline uses kilograms or pounds for fuel and weight figures |

### Admin Account
| Field | Description |
|---|---|
| Full name | Display name for the admin user |
| Email address | Login email |
| Password | Minimum 8 characters, must be confirmed |

After submitting, the admin account is created with the **Super-Admin** role, the first airline is created, and the admin is logged in and taken directly to the dashboard. The setup page is permanently disabled once any user exists.

---

## 2. Registering an Account

New users register at `/register` with their name, email address, and a password. After registration, the system redirects to the **Airline Portal** — a new account has no airline membership yet, so this is the starting point for everything.

---

## 3. The Airline Portal

The Airline Portal at `/portal` is always accessible from the navigation bar. It is the central hub for managing your airline memberships and is the first page new users see.

From the portal you can:

- **View your current memberships** — see every airline you belong to and your role within each
- **Switch your active airline** — change which airline is currently active in your session
- **Join an airline** — redeem an invite code to become a member of an existing airline
- **Found a new airline** — create your own airline and become its first Manager (visible only if permitted by the instance — see [Founding a New Airline](#5-founding-a-new-airline))

> Most of the app (dashboard, flight filing, fleet management) depends on having an active airline selected. If you navigate to a page that requires an active airline and none is set, you will be redirected to the portal automatically.

---

## 4. Joining an Airline via Invite Code

To join an existing airline, you need an **invite code** from one of that airline's Managers.

Invite codes have the format `{ICAO}-{4 digits}`, for example `DLH-4918`.

**To redeem a code:**
1. Go to the **Airline Portal** (`/portal`)
2. Enter the code in the **Join an Airline** section
3. Click **Redeem Code**

If the code is valid and unused, you are immediately enrolled in the airline with the role the Manager assigned to the code. If you have no other active airline, this airline is automatically set as your active one and you are redirected to the dashboard.

Each invite code is single-use — it cannot be redeemed again after it has been used.

---

## 5. Founding a New Airline

If the instance allows it (or you are a Super-Admin), the **Found a New Airline** card appears on the Airline Portal. Clicking it opens the founding form at `/airline/found`.

Fill in the same details as the setup wizard's airline section (name, IATA prefix, ICAO callsign, ATC callsign, hub, country, and optional fields for website, description, founding date, and weight unit).

On submission, the new airline is created, you are automatically enrolled as its **Manager**, and it becomes your active airline. You are redirected to the dashboard with a confirmation message.

> From this point you can generate invite codes to invite other pilots to your airline.

---

## 6. Switching Between Airlines

If you are a member of more than one airline, you can switch which one is active at any time from the **Airline Portal**.

Your membership list shows all your airlines and your role in each. Click **Switch** next to any airline to make it the active one. All pages (dashboard, flight list, fleet manager) will then show data for the newly selected airline.

---

## 7. The Dashboard

After logging in (and with an active airline set), you land on the dashboard at `/user/dashboard`.

The dashboard shows:

- **Your stats** for the active airline — total accepted flights and total logged hours
- **Recent airline flights** — the 5 most recently accepted PIREPs across the whole airline

---

## 8. Filing a PIREP

A PIREP (Pilot Report) is filed after completing a flight. Navigate to **File a Flight** in the navigation bar (`/user/flights/add`).

| Field | Description |
|---|---|
| Flight number | Numeric part of the flight number, 1–4 digits (e.g. `400` for `LH400`) |
| Callsign suffix | Optional suffix appended to the ICAO callsign (e.g. `1` in `DLH1`, up to 4 digits optionally followed by 2 letters) |
| Departure airport | ICAO code of the departure airport |
| Arrival airport | ICAO code of the arrival airport |
| Aircraft | Select from the airline's active fleet |
| Cruise altitude | Planned/flown cruise altitude in feet, up to 50,000 |
| Block off | Date and time wheels-up (departure block time) |
| Block on | Date and time wheels-on (arrival block time) |
| Fuel burned | Total fuel consumed during the flight |
| Route | Filed ATC route string |
| Online network | The network you flew on (VATSIM, IVAO, PilotEdge, or Offline) |
| Remarks | Any additional notes (optional) |

After filing, the PIREP is submitted with **Pending** status. If the airline has PIREP review enabled, all Managers are notified and a Manager must accept or reject it. You will receive an in-app notification with the outcome.

---

## 9. Viewing Your Flights

Your personal flight log for the active airline is available at **My Flights** in the navigation. It shows all PIREPs you have filed, with their current status (Pending, Accepted, or Rejected).

You can search by flight number or PIREP ID. Click any flight to view its full details, including route, block times, distance flown, and any rejection remarks.

---

## 10. Manager: Reviewing PIREPs

Managers (and users with the `review flight` permission) can review pending PIREPs at `/user/flights/review`.

The review queue lists all **Pending** PIREPs for the active airline in reverse chronological order. For each flight you can:

- **Accept** — the PIREP is marked Accepted, the pilot is notified, and the flight counts toward their logged hours and the aircraft's total
- **Reject** — the PIREP is marked Rejected; you may optionally provide a reason which is included in the pilot's notification

Both actions send an in-app notification to the pilot.

---

## 11. Manager: Invite Codes

Managers can generate and manage invite codes for their airline at `/airline/invitecodes`.

**Generating a code:**
1. Select the role the invitee will receive on joining (**Pilot**, **Dispatcher**, or **Manager**)
2. Click **Generate Code**

The code is generated automatically in the format `{ICAO}-{4 digits}` (e.g. `DLH-4918`) and appears in the code list. Share it directly with the person you want to invite.

**Deleting a code:**
Unused codes can be deleted from the list. Codes that have already been redeemed cannot be deleted.

---

## 12. Manager: Fleet Management

Fleet management is available to Managers at `/airline/fleetmanager`.

### Viewing the Fleet

The fleet manager lists all aircraft registered to the active airline. You can search by registration, manufacturer, model, or current location, and sort by any column. Each row shows the aircraft's registration, type, current location, total logged hours, and active status.

Click any aircraft to view its full detail page, including a map of its current location and its last 5 accepted flights.

### Adding an Aircraft

Navigate to **Add Aircraft** (requires the `add aircraft` permission). Fill in:

| Field | Description |
|---|---|
| Registration | Tail number in ICAO format (e.g. `D-AIBL`) |
| Manufacturer | Aircraft manufacturer (e.g. `Airbus`) |
| Model | Aircraft type (e.g. `A320-214`) |
| Engine type | Engine variant (e.g. `CFM56-5B4`) |
| Current location | ICAO code of the airport where the aircraft currently is |
| SATCOM | Whether the aircraft is equipped with satellite communications |
| Winglets | Whether the aircraft has winglets |
| SELCAL code | Selective calling code, format `AB-CD` (optional) |
| Mode S hex code | 6-character hexadecimal transponder code (optional) |
| MSN | Manufacturer serial number, up to 6 digits (optional) |
| MTOW | Maximum take-off weight (optional) |
| MZFW | Maximum zero-fuel weight (optional) |
| MLW | Maximum landing weight (optional) |
| Remarks | Free-text notes (optional) |

Tail numbers must be unique among active aircraft within the airline.

### Editing an Aircraft

Click **Edit** on any aircraft (requires the `edit aircraft` permission). All fields except the current location can be updated. The active/inactive status can also be toggled here — setting an aircraft to inactive removes it from the fleet selection when filing PIREPs.

---

## 13. Notifications

YAAMS sends in-app notifications for key events. The notification bell in the navigation bar shows a badge when you have unread notifications.

Events that trigger a notification:
- **PIREP filed** — all Managers of the airline receive a notification when a new PIREP is submitted
- **PIREP accepted** — the pilot receives a notification with a link to the accepted flight
- **PIREP rejected** — the pilot receives a notification including the rejection reason (if provided)

Open the notifications page at `/user/notifications` to see all your notifications. Click **Acknowledge** to mark a notification as read.

---

## 14. Roles & Permissions

YAAMS has two separate role systems that work in parallel.

### Global Roles (Spatie)

Assigned per user, instance-wide:

| Role | Description |
|---|---|
| **Pilot** | Standard user — can file PIREPs and view their own flight log |
| **Manager** | Can add/edit aircraft, generate invite codes, and review PIREPs |
| **Super-Admin** | Bypasses all permission checks; can do everything regardless of airline membership or other roles |

### Per-Airline Roles

Stored on each airline membership individually. A user can have a different role in each airline they belong to:

| Role | Description |
|---|---|
| **Pilot** | Can file PIREPs for this airline |
| **Dispatcher** | Reserved for future use |
| **Manager** | Can generate invite codes and review PIREPs for this airline |

The per-airline Manager role is what controls day-to-day airline operations. A user does not need the global Manager role — only the per-airline one — to manage invite codes and review flights for their airline.
