# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

**Primary: virtual airline managers.** These are flight-sim community members who run a virtual airline (VA) on a YAAMS instance, holding the per-airline `Manager` role (the owner always has it). Their work is administrative and repeats week after week: reviewing and accepting/rejecting PIREPs, maintaining the fleet (adding airframes, grounding/retiring them, relocating aircraft), managing members and roles, issuing invite codes, posting NOTAMs, moderating screenshot uploads, and setting airline-wide rules (PIREP review, location continuity). They work at a desk, often in short sessions alongside their own simming.

**Secondary audiences:**
- **Pilots** file PIREPs after a flight, check their verified hours and stats, contribute aircraft screenshots, and switch between the airlines they belong to. They visit most often, but each visit is short and focused on one task.
- **Dispatchers** review PIREPs for an airline but do not manage it.
- **Instance operators (Super-Admins)** self-host YAAMS for several airlines. They handle setup, instance settings, legal pages, user and airline administration, the activity log and monitoring.

## Product Purpose

YAAMS (Yet Another Airline Management System) is an open-source (GPL v3) platform for running virtual airlines. It records flights through a PIREP workflow, tracks fleets and airframe state, verifies pilot hours, and gives a VA's staff the tools to run the community. Success means a manager can keep an airline consistent and active with little admin overhead, and pilots trust that their logged hours and history are accurate.

## Positioning

- **Decentralised and self-hosted:** anyone can run their own instance, and no central service owns the data.
- **Multi-airline per instance:** one instance hosts many airlines. Users hold a separate role in each airline and switch between memberships, and an invite-code flow handles joining.
- **Opt-in realism:** each airline chooses its own rules. Location continuity means aircraft only depart from where they last landed and move when a flight is filed. PIREP review can be required or skipped, and flight data stays in aviation conventions (UTC/Zulu, ICAO codes, nautical miles).

## Operating Context

- Server-rendered web app. Each user keeps an active airline in their session, and most screens are scoped to it. Suspended airlines remain viewable but cannot be changed.
- The domain uses aviation vocabulary: PIREP, ICAO/ATC callsign, block-off/block-on, NOTAM, hub, registration, current location, cruise altitude, burned fuel. Flight times are always shown in Zulu. Only crew- and admin-facing timestamps (such as NOTAM expiry) use the instance timezone.
- Flight statuses: Pending, Accepted, Rejected. Aircraft lifecycle: active, inactive (reversible), retired (permanent).
- A REST API (`/api/v1`, documented at `/docs`) serves ACARS and desktop clients such as YAAMS Commander. Role-aware clients read capability flags from it.
- Notifications arrive in-app (bell) and, depending on the user's preference, by email.

## Capabilities and Constraints

- **Stack is fixed:** Laravel 13 + Blade + Bootstrap 5.3 with vanilla CSS on top (`public/css/yaams-theme.css`). No SPA and no new frontend framework.
- **Operator-brandable:** the instance name (`app_name` setting) appears in every title and header. The UI must not hardcode "YAAMS" as the identity of an instance, and page titles follow `{Page} - {Instance Name}`.
- Five layouts (`app`, `landing`, `loginlayout`, `setuplayout`, `error`) share a backdrop and legal footer links. Error pages must render without session, auth or DB access.
- Admin pages follow one fixed structure: page header, sidebar and entity header (see CLAUDE.md, "Admin Area Layout").
- Per-airline permissions come from `AirlinePolicy`. What a user sees depends on their role, and suspension makes an airline read-only.
- Legal pages (Imprint, Privacy, Terms) and a GDPR data export exist because instances may run in the EU.
- **Open:** dark mode is not implemented today, and nobody has decided whether it should be.

## Brand Commitments

- Name: **YAAMS - Yet Another Airline Management System**. Tagline in use: "Track PIREPs, manage fleets, and build thriving virtual airline communities - all in one place."
- Logo assets: `public/img/yaams-logo-icon.svg`, `public/img/yaams-nexttoeach.svg`, `public/img/email-logo.png`.
- The chart-grid backdrop with a dashed route ending at an airplane icon is an established motif (`layouts/_backdrop`).

## Evidence on Hand

- Live counts on the landing page, when the operator enables them: airlines, pilots, flights logged, hours flown.
- Screenshots in the GitHub README and wiki.
- There are no testimonials, user counts across instances, or named VA customers. Do not invent any.

## Product Principles

1. **Managers' time is the scarce resource.** Review, fleet and member workflows should be fast to scan and act on in repeated short sessions.
2. **Records are trustworthy.** History is never silently rewritten: retired aircraft keep their flights, actions are audit-logged, and hours only count accepted flights.
3. **Realism is opt-in.** Each airline picks its own rules, and the product never forces simulation strictness on a community that doesn't want it.
4. **The instance belongs to its operator.** Branding, registration, legal text and policies are theirs to set, and the platform recedes behind them.
5. **Web and API agree.** Anything a manager or pilot can do on the web behaves the same through the API.

## Accessibility & Inclusion

Target **WCAG 2.2 AA**: contrast, full keyboard operation, visible focus. Respect reduced motion (the animated login backdrop already does).
