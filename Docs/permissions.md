# Permissions & Roles

YAAMS uses a two-layer authorization system:

1. **Per-airline roles** — stored in the `airline_memberships` table. Every user has exactly one role per airline they belong to. This is the primary mechanism for controlling what a user can do within a specific airline.
2. **Global Super-Admin role** — managed by [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission). A Super-Admin bypasses all permission checks across the entire instance via a `Gate::before` rule.

---

## Per-Airline Roles

The `role` column in `airline_memberships` accepts one of three values:

### Pilot
The default role assigned to every new member. Pilots can interact with the system but cannot modify shared airline resources.

**Can:**
- File PIREPs (flight reports)
- View their own flight history
- View the fleet (read-only)
- View flight details

**Cannot:**
- Review, accept, or reject PIREPs filed by other pilots
- Add or edit aircraft
- Manage airline membership

---

### Dispatcher
A Dispatcher handles flight operations review. This role is intended for staff members who need to process incoming PIREPs without having full administrative access to the airline.

**Can do everything a Pilot can, plus:**
- View pending PIREPs in the review queue
- Accept PIREPs
- Reject PIREPs (with remarks)
- Receive in-app notifications when a new PIREP is filed

**Cannot:**
- Add or edit aircraft
- Manage airline membership

---

### Manager
The airline administrator. A Manager has full control over everything within their airline.

**Can do everything a Dispatcher can, plus:**
- Add aircraft to the fleet
- Edit aircraft details and status
- Manage airline membership

---

## Permission Matrix

| Action | Pilot | Dispatcher | Manager | Super-Admin |
|---|:---:|:---:|:---:|:---:|
| File PIREPs | ✓ | ✓ | ✓ | ✓ |
| View fleet | ✓ | ✓ | ✓ | ✓ |
| View flight details | ✓ | ✓ | ✓ | ✓ |
| Review PIREPs (accept/reject) | — | ✓ | ✓ | ✓ |
| Receive PIREP notifications | — | ✓ | ✓ | ✓ |
| Add aircraft | — | — | ✓ | ✓ |
| Edit aircraft | — | — | ✓ | ✓ |
| Manage membership | — | — | ✓ | ✓ |
| Instance-wide admin | — | — | — | ✓ |

---

## Super-Admin

The Super-Admin role is a global, instance-wide role managed separately from per-airline roles. It is not scoped to any single airline.

A Super-Admin:
- Bypasses **all** permission checks on every airline on the instance
- Is created automatically during the setup wizard
- Can act as Manager in any airline without holding an explicit airline membership role

> The Super-Admin role is implemented via Spatie Laravel Permission's `Gate::before` hook in `AuthServiceProvider`. It does not interact with the `airline_memberships.role` column.

---

## Implementation Notes

Permission checks in controllers and policies use the `User::hasAirlineRole(Airline, string|array)` helper:

```php
// True for Dispatcher or Manager of the given airline
$user->hasAirlineRole($airline, ['Dispatcher', 'Manager']);

// Convenience shorthands
$user->isManagerOf($airline);        // role = Manager
$user->canReviewFlightsFor($airline); // role = Dispatcher or Manager
```

To add a new role in future:
1. Add the new value to the `role` enum in a migration
2. Add it to the relevant `hasAirlineRole()` calls in controllers/policies
3. Document it here
