# Homzen Static Mirror → Professional Laravel Rebuild Blueprint

## 1. What was actually uploaded

The four ZIPs were fully unpacked and inventoried.

- `homzen.botble.com.zip`: 316 files, ~77 MB. Main/default-locale HTML snapshots plus theme/vendor/storage assets.
- `lang.zip`: 556 files, ~185 MB. Localized HTML snapshots under `en/`, `ar/`, `fr/`.
- `id.zip`: 193 files, ~64 MB. Indonesian localized HTML snapshots.
- `vi.zip`: 193 files, ~65 MB. Vietnamese localized HTML snapshots.
- Total inventoried files: **1,258**.
- Total HTML snapshots detected: **1,132**.
- Backend source files found: **0** (`*.php`, `*.blade.php`, `composer.json`, `artisan`, `*.sql`, `.env`, `package.json` are absent).

Conclusion: this is a downloaded/static mirror of a running site, not the Laravel source project and not a database backup.

## 2. Detected product/version

The HTML and assets identify the site as Botble **Homzen**. Theme/core asset query strings repeatedly contain `v=1.3.3`; the snapshot therefore matches Homzen 1.3.3-era output.

Observed plugin/frontend namespaces:

- Real Estate
- Language / Language Advanced-style frontend
- Social Login
- Newsletter
- Contact
- Announcement
- Mortgage Calculator (`fob-mortgage-calculator`)
- Botble core ACL/admin frontend assets
- Homzen theme assets
- Leaflet + marker clustering
- Swiper
- Fancybox
- Bootstrap, including RTL build

## 3. Languages and RTL

Recovered languages:

- English (`en`)
- Arabic (`ar`)
- French (`fr`)
- Indonesian (`id`)
- Vietnamese (`vi`)

Arabic pages load `bootstrap.rtl.min.css`. A rebuild should use one Blade/UI implementation and localized content, not duplicated full HTML files per language.

Recommended localization strategy:

- `languages` table for active languages and direction.
- Per-domain translation tables such as `property_translations`, `project_translations`, etc.
- `resources/lang/{locale}` or JSON language files for static UI strings.
- Locale middleware and URL prefixing.
- Locale-specific slug + SEO fields.
- `hreflang`, canonical URLs, localized sitemap entries.

## 4. Recovered site inventory

From the main mirror and the matching locale paths, the following entities are directly recoverable from public HTML:

- Properties: **62** detail pages
- Projects: **18** detail pages
- Agents: **12** public profile pages
- Blog/news posts: **24** detail pages
- Careers: **6** detail pages
- Property categories visible in routes: Apartment, Commercial property, Condo, House, Land, Villa
- Blog tags visible in routes: 8
- Currencies visible: USD, EUR, VND
- Home variants: 5
- Static/public pages include About, Services, Pricing, Contact, FAQ, Privacy, Cookie Policy, Coming Soon, Wishlist, Register, Blog

The recoverable catalog is exported separately as `recovered_catalog.json`.

## 5. Observed public routes and actions

### Real-estate browsing

- Property listing/search/filter/pagination
- Property category pages
- Sale/rent filtering
- Project listing/search/filter/pagination
- Property/project detail pages
- Map markers and Leaflet clustering
- City AJAX search
- Project autocomplete AJAX search
- Property map AJAX data
- Generic site search

Observed filter fields include keyword, location, category, price range, square range, bedroom, bathroom, floor, type, project, amenities. Project filters additionally include flats and blocks.

### Property/project detail

Observed details include:

- Property/project identifiers
- Category/type
- Bedrooms, bathrooms, floors, square area
- Address and coordinates
- Project investor, block count, flat count, finish date and open-sale date
- Amenities
- Nearby facilities + distance
- Image galleries
- Related/latest properties
- Reviews
- Consultation / schedule-a-tour form
- Mortgage calculator
- Social sharing
- Wishlist

### Authentication

Observed forms/routes:

- Login
- Register
- Password reset request
- Admin password reset snapshot
- Social providers: Facebook, Google, GitHub, LinkedIn, LinkedIn OpenID Connect

### Engagement / CRM

Observed actions:

- Review submission: `/ajax/review/{slug}`
- Consultation: `/send-consult`
- Contact: `/contact/send`
- Newsletter: `/newsletter/subscribe`
- Wishlist toggle

### Content

- Blog/posts
- Tags
- Careers
- Pages
- RSS feeds for posts, properties, projects
- Sitemap
- Cookie consent
- Announcements

## 6. Important archive issues

### The archive is not a backend project

There are no controllers, models, migrations, policies, jobs, mailables, events, commands, tests or actual database schema. Hidden business logic cannot be recovered exactly from these files.

### Query variants are saved as filenames

Examples such as `properties?page=4` and `properties?type=sale&page=3` are downloaded responses, not application files. In Laravel they should become one route/controller/query pipeline.

### Locale duplication

The current mirror stores full rendered pages per language. A Laravel rebuild must normalize this into shared entities plus translation rows.

### Missing media

The HTML references multiple media assets that are not present in the archive. At least 40 unique `storage/...` image paths referenced by rendered pages are missing, including generated property thumbnails, agent avatars, floor-plan images and some post/homepage images. The original media library or source server backup is needed for pixel-perfect restoration.

### Admin/OAuth snapshots should not be migrated as public pages

Files under `admin/...` and downloaded `auth/...` responses are rendered snapshots and must not be copied into public Laravel views. Social authentication should be reimplemented using OAuth flows.

## 7. Recommended technical baseline

- Laravel 13
- PHP 8.3+ (PHP 8.4 is suitable when dependencies support it)
- MySQL 8.x with `utf8mb4`
- Redis for cache/queues/rate limiting where available
- Laravel Sanctum for first-party/mobile API auth
- Laravel Socialite for social login
- Queue-based email and media processing
- Vite for frontend asset building
- Blade + Alpine/vanilla JS for server-rendered pages, or Inertia only if the team explicitly wants an SPA-like admin
- Object storage abstraction through Laravel Filesystem for S3-compatible storage

## 8. Professional modular-monolith structure

```text
app/
├── Modules/
│   ├── Identity/
│   │   ├── Application/{Actions,DTOs,Queries}
│   │   ├── Domain/{Models,Enums,Events,Policies}
│   │   ├── Http/{Controllers,Requests,Resources}
│   │   ├── Infrastructure/{Persistence,Providers}
│   │   └── routes/{web.php,api.php}
│   ├── Property/
│   ├── Project/
│   ├── Agent/
│   ├── Location/
│   ├── Search/
│   ├── Wishlist/
│   ├── Review/
│   ├── Lead/
│   ├── Billing/
│   ├── Content/
│   ├── Career/
│   ├── Newsletter/
│   ├── Localization/
│   ├── Currency/
│   ├── Media/
│   ├── Seo/
│   ├── Settings/
│   ├── Notification/
│   └── Audit/
├── Support/
│   ├── Money/
│   ├── Slugs/
│   ├── Pagination/
│   └── Helpers/
└── Providers/

resources/
├── views/
│   ├── layouts/
│   ├── components/
│   └── modules/
└── lang/{en,ar,fr,id,vi}/

routes/
├── web.php
├── api.php
└── admin.php

database/
├── migrations/
├── seeders/
└── factories/

tests/
├── Feature/Modules/
└── Unit/Modules/
```

Each module owns its requests, controllers, actions, domain models/policies, resources and tests. Cross-module interaction should happen through Actions/Services/Events instead of directly coupling controllers to unrelated models.

## 9. Database design

### Identity and authorization

- `users`
- `agent_profiles`
- `agencies`
- `agency_user`
- role/permission tables
- OAuth identities (`social_accounts`)
- password reset/session/token tables

### Localization

- `languages`
- dedicated translation tables per translatable domain
- locale-specific slug and SEO columns in translation tables

### Location

- `locations` (hierarchical country/state/city/area)
- `location_translations`

### Property

- `properties`
- `property_translations`
- `property_media`
- `property_amenity`
- `property_nearby_places`
- `property_custom_values` (only for genuinely dynamic custom fields)
- optional `property_status_history`

Core property columns should include owner, agency, project, category, purpose, lifecycle status, price + currency, bedrooms, bathrooms, floors, area, coordinates, location, featured state, publish/expiry dates, view count and audit fields.

### Project

- `projects`
- `project_translations`
- `project_category`
- `project_media`
- `project_floor_plans`
- `project_amenity`
- `project_nearby_places`
- relation to properties through `properties.project_id`

### Taxonomy

- `property_categories`
- `property_category_translations`
- `amenities`
- `amenity_translations`
- `nearby_place_types`
- `nearby_place_type_translations`

### Engagement

- polymorphic `reviews`
- polymorphic `favorites` / wishlist
- polymorphic `leads` for property/project consultation
- `contact_messages`

### Billing / credits

- `credit_packages`
- `orders`
- `payments`
- `invoices`
- `credit_ledger`
- optional `refunds`

Do not store only a mutable `credits` integer. Use a ledger and derive/transactionally update balance to preserve auditability.

### Content/CMS

- `pages` + `page_translations`
- `content_blocks` + `content_block_translations`
- `posts` + `post_translations`
- `post_categories` + translations
- `tags` + translations
- post pivots
- `careers` + `career_translations`
- `menus`, `menu_items`, translations
- `testimonials`, translations
- `announcements`, translations

### Media / settings / system

- `media_files`
- optional `media_translations` for alt/caption
- `currencies`
- `settings`
- `setting_translations`
- `audit_logs`
- Laravel notifications/jobs/failed_jobs tables as required

## 10. Critical DB rules

- Money: `DECIMAL`, never float.
- Coordinates: fixed decimal precision.
- Unique localized slugs: `(locale, slug)`.
- All many-to-many pivots use composite unique constraints.
- Payment gateway transaction IDs are unique.
- Review rating constrained to 1–5.
- Foreign keys + indexes on every filtering/join column.
- Soft delete only where business recovery is valuable; do not blindly soft-delete pivots.
- Property list indexes should cover `(status, purpose, category_id, location_id, published_at)` and common price/project filters.
- Searchable amenities remain normalized in pivots rather than JSON.
- JSON is appropriate for non-filtered block/theme configuration, provider payload snapshots and rare custom metadata.

## 11. Business cycles, separated

### A. Public property discovery

Browse/search → apply filters → map/list results → open detail → wishlist/share → contact/review.

### B. Agent onboarding/authentication

Register → verify email/phone if enabled → complete agent profile → optional admin/KYC approval → dashboard access.

### C. Property publishing

Agent creates draft → uploads media → enters location/pricing/specs → assigns category/project/amenities → validates available listing credit → submit for moderation → admin approves/rejects → publish → expiry/auto-renew → archive/republish.

### D. Project publishing

Admin/authorized agent creates project → translated content → investor/dates/blocks/flats/floors → categories/amenities/floor plans/location → related properties → moderation → publish.

### E. Consultation/lead

Visitor submits property/project inquiry → validation/rate-limit → store lead → notify listing owner/admin → assign → status follow-up → notes/history → close/won/lost.

### F. Review

Authenticated user → rating/content → validation → pending moderation → approve/reject → aggregate rating refresh.

### G. Wishlist

Guest uses session/local state → authenticated user persists favorites → merge guest favorites after login.

### H. Credits/payment

Select package → create order → hosted/payment gateway flow → verify webhook idempotently → record payment → credit ledger entry → invoice → property publish consumes credit transactionally.

### I. Content publishing

Create page/post/career → translate → SEO/slug → draft/review/publish → sitemap/RSS/cache invalidation.

### J. Localization

Detect/select locale → load locale-specific translation row → apply RTL for Arabic → preserve locale through forms/emails → emit hreflang/canonical.

### K. Currency

Choose currency → persist preference → convert display amounts from base/recorded currency → never alter original stored monetary value just because display currency changes.

### L. Mortgage calculator

Client-side calculation service with shared configuration; no database write is required unless storing user scenarios is a product requirement.

## 12. API shape

Version public/mobile APIs under `/api/v1`.

Suggested resources:

- auth/profile
- properties
- projects
- agents
- categories/amenities/locations
- favorites
- reviews
- leads/consultations
- packages/orders/payments (authenticated)

Use Form Requests, Policies, API Resources and Sanctum. Keep admin write APIs separate by permission.

## 13. Rebuild order

1. Freeze/recover content and original media.
2. Create Laravel 13 base + authentication/ACL.
3. Build localization and shared taxonomy/location layer first.
4. Build Property module and filters/map.
5. Build Project module and relationship to properties.
6. Build Agent dashboard and publishing workflow.
7. Build Leads, reviews and wishlist.
8. Build Billing/credits only after listing workflow is stable.
9. Build CMS/blog/careers/homepage blocks.
10. Migrate recovered multilingual content.
11. Rebuild SEO/sitemap/RSS/cookie/announcement behavior.
12. Add queues, cache, rate limiting, monitoring and audit logs.
13. Add feature tests for every cycle before cutover.

## 14. Testing minimum

Feature tests should cover:

- every locale and Arabic RTL rendering
- localized slug resolution
- all property/project filters
- authorization/ownership boundaries
- property moderation lifecycle
- credit consumption concurrency
- payment webhook idempotency
- review authorization/moderation
- wishlist merge
- lead routing/notification
- media authorization
- sitemap/hreflang/canonical output
- currency display conversion
- social login callback failures

## 15. What is still required for an exact 1:1 migration

For a true migration rather than a behavioral rebuild, obtain at least one of:

- the original Homzen/Botble source package for the deployed version, ideally a licensed current package; and
- the production database dump; and
- the complete `storage`/media directory (or cloud bucket export); and
- relevant `.env` settings with secrets removed but service choices documented.

Without those, public content can be reconstructed from the mirror, but hidden account data, payments, credits, moderation states, admin configuration, historical leads, reviews not rendered publicly, and exact backend rules cannot be recovered faithfully.
