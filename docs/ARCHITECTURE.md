# Architecture

Modular monolith. Primary domain folders live under `app/Modules/`; Eloquent models remain under `app/Models/` for a simple Laravel integration boundary. Controllers are thin; cross-step business rules should be implemented in `Application/Actions` and queries in `Application/Queries`.

Cycles: Identity → Agent/Agency → Property → Moderation → Publishing → Search → Lead → Appointment → Review. Project inventory is a separate cycle but properties may link to projects. Billing uses immutable credit-ledger records rather than a naked balance field.
