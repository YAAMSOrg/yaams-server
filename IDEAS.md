# YAAMS Feature Ideas & Roadmap (Nerd Edition)

This document tracks ideas for increasing the realism and detail of the YAAMS (Yet Another Airline Management System) platform, specifically targeting flight simulation enthusiasts.

## 1. Aircraft (Fleet & Technical)
Increasing the level of detail for airframes.

- **MSN (Manufacturer Serial Number):** Unique serial number from the manufacturer.
- **Engine Type:** Specific engine variants (e.g., CFM56-5B4 vs. IAE V2527-A5).
- **Maintenance Status:**
    - Tracking of `Total Cycles` (landings) and `Airframe Hours`.
    - Maintenance intervals (A, B, C, D-Checks) with downtime.
- **Technical Codes:**
    - **SELCAL:** 4-character radio code (e.g., AB-CD).
    - **HEX Code:** Mode-S transponder address.
- **Configuration (LOPA):** Seating configuration (First/Business/Premium Eco/Economy).
- **Weights (Performance):** MTOW (Max Takeoff), MZFW (Max Zero Fuel), MLW (Max Landing Weight).
- **Visual Options:** Presence of winglets, SATCOM humps, etc.
- **Registry & Transfer History (Multi-VA Trading / Leasing):**
    - **Ownership & Leasing:** Allow airlines to sell or lease (wet/dry lease) aircraft to other Virtual Airlines on the same YAAMS instance.
    - **Aircraft Registration Log:** A chronological history log of ownership changes and leases (e.g., *"Delivered to Airline A on 2026-01-01"*, *"Leased to Airline B on 2026-06-15"*, *"Sold to Airline C on 2027-01-10"*), mimicking real-world spotter sites like Planespotters or Airfleets.
    - **Instance-Wide Aircraft Registry:** A public directory of all airframes on the YAAMS instance, allowing spotters and pilots to look up any aircraft by registration/MSN and see its complete history.
    - **Flight Log Integrity on Transfer:** When an aircraft is transferred to another airline, its historical flight logs remain associated with the airline that originally operated them, while the physical airframe moves to the new airline carrying over its accumulated hours, MSN, engine type, and maintenance history.

## 2. Flight / PIREP (Pilot Reports)
Detailed analysis of flight execution.

- **Landing Performance:**
    - `Landing Rate` in ft/min (fpm).
    - `G-Force` at touchdown.
- **Payload & Loadsheet:**
    - Breakdown into passengers (Adult/Child), cargo, and mail.
    - ZFW (Zero Fuel Weight) calculation.
- **Fuel Management:**
    - Breakdown: Trip, Contingency, Alternate, Final Reserve, Taxi Fuel.
    - `Burned Fuel` vs. `Planned Fuel` (efficiency check).
- **Flight Path & Profile:**
    - `Step Climbs` (altitude changes).
    - `Cost Index` (economy).
    - `V-Speeds` (V1, Vr, V2, Vref).
- **Network Integration:**
    - Pilot CID (VATSIM/IVAO).
    - Link to external trackers (SimAware, Volanta, etc.).
- **IATA Delay Codes:** Reason for delays (e.g., Code 89 for de-icing).

## 3. Airline (Management & Business)
In-depth simulation of airline operations.

- **Hub Management:** Definition of home airports and bases.
- **Alliances:** Joining virtual alliances with shared benefits.
- **Economy Simulation:**
    - Virtual account for the airline.
    - Costs for fuel, landing fees, maintenance, and salaries.
- **Reputation System:** Impact of punctuality and landing quality on airline rating.
- **Type Rating System:** Career mode (e.g., accumulate hours on single-engine aircraft before heavy jets are allowed).
- **SOP (Standard Operating Procedures):** Storing manuals (PDF) for pilots.

## 4. Infrastructure & Integrations
- **SimBrief API:** Import flight plans directly into the dispatch system.
- **ACARS Connection:** Own or third-party client for automatic tracking.
