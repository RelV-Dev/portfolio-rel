# RelV portfolio

PHP portfolio with a Supabase gallery, moderated guestbook, private admin dashboard,
and a server-side APInex assistant. Requires PHP 8+ with curl, DOM, and mbstring.

## Apply database changes before deploying this revision

1. Back up the current database. On a new database only, run `schema.sql`,
   `gallery_schema.sql`, and `guestbook.sql` first. Do not rerun seeds on production.
2. Run `migrations/001_public_access.sql` in the Supabase SQL Editor. It replaces
   the policies on these three application tables, creates the explicit admin
   allowlist, a public testimonial view, and an atomic shared rate limiter.
3. Confirm `rel@relv.biz.id` exists under Supabase → Authentication → Users, then
   run `migrations/002_enrol_admin.sql` in the SQL Editor. The dashboard fails closed
   until this account is enrolled. Passwords and browser-editable profile
   metadata cannot confer admin access.
4. In Vercel, open the project → Settings → Environment Variables. Add the values below for Production (and Preview if you test preview deployments). In Supabase, open Project Settings → API and copy the server-only `service_role` secret. In APInex, create/copy your API key. Generate `RATE_LIMIT_SECRET` in a terminal with `openssl rand -hex 32`. Add each value in Vercel one at a time; never commit them or enter them in browser JavaScript. Then redeploy so the PHP functions receive the new values. Do not paste secrets into chat.

   Add these exact names:
   `APINEX_API_KEY`, optional `APINEX_MODEL`, `SUPABASE_SERVICE_ROLE_KEY`, and
   a random `RATE_LIMIT_SECRET`. `APINEX_MODEL` may remain `gpt-6.1-sol`. The
   `.env.example` file lists variable names but contains no secret values.
5. After redeploy, verify admin login, guestbook submission/moderation,
   gallery edits, a public testimonial, and a real AI response. If the dashboard denies
   the account, check the exact email in Supabase Auth Users and rerun the enrolment SQL.

The migration was tested in an isolated PostgreSQL-compatible PGlite database,
not applied to production by this checkout. Do not deploy only the UI without
these migration and environment steps: the new UI intentionally does not fall
back to the old unrestricted tables or unprotected endpoints.

## Privacy and access

Public testimonials read `public_testimonials`, which excludes transfer proof,
chat screenshots, transaction dates, and prices. Project images are hidden by
 default until the admin explicitly approves publication with the new checkbox.
The original records are retained for the admin. Image URLs previously published
or copied elsewhere are not revoked by a database migration; remove or replace
those assets in Cloudinary if they must cease being publicly accessible.

The public guestbook can no longer insert directly into Supabase. The server
validates requests, enforces a distributed one-per-minute limit per IP, and sets
approval to false. AI allows ten requests per minute per IP. The limiter fails
closed when its configuration or database is unavailable. Bucket keys are HMACs,
not raw IP addresses. Vercel's platform-controlled forwarding header identifies
clients; other hosts use REMOTE_ADDR and should not trust arbitrary forwarded headers.

## Cache and public data

The public page uses `Cache-Control: no-store` and reads current visible gallery
rows on every request. This trades some backend latency for correct updates,
including newly hidden records. The old clear-cache endpoint returns 410 instead
of pretending to invalidate the CDN. Database failure displays a retry state;
no stale local cache is served. Existing `api/cache_gallery.json` is untouched
and is no longer a runtime input.

## AI behavior

APInex reference: https://apinex.bond/developers/models/chat

`GET /api/chat.php` reports configuration readiness, not upstream health.
`POST /api/chat.php` accepts `message` (up to 1,000 characters), optional
`previous_question`, and `previous_source_ids`. Request size is limited to 8 KB.
Only the server's source catalogue is factual evidence. Previous questions are
untrusted context used to resolve references, never knowledge. Source IDs are
validated before the server returns original excerpts and anchor links. Model
prose cannot be displayed. Unknown IDs fail closed. This favors strict grounding
rather than generated paraphrasing; irrelevant source selection is still possible.

`data/journey.json` supplies both journey modal details and AI evidence. Other
sources are the page's public profile/skills/contact sections and the same live
gallery loader as the page. Gallery links reveal the right pagination page.
Visitor messages and private testimonial fields are excluded. Each question and
public source catalogue is sent to APInex; history in browser storage is only for
display and expires after ten minutes. Follow-up context resets on page reload.

## Local development and validation

`php -S localhost:8765`, then open `http://localhost:8765/api/index.php`.
PHP does not automatically load `.env` files; export variables or set them in hosting.

- `php tests/assistant.php`
- With the PHP server running and secrets unset: `python tests/endpoint_test.py`
- `node tests/database.cjs` requires `@electric-sql/pglite`.
- `node tests/browser.cjs` requires Playwright and Chromium. It tests the real
  local page with intercepted public testimonial/guestbook fixture responses;
  it does not mutate production data. Screenshots are written to `/tmp`.
- `php -l api/index.php`, `php -l api/chat.php`, `php -l api/guestbook.php`
- `node --check dashboard.js`, `node --check portfolio-ui.js`

For ephemeral QA installs outside this buildless repository, set NODE_PATH to the
installation's node_modules directory and PLAYWRIGHT_BROWSERS_PATH to its browser
cache directory. No Node dependency is required at runtime.

## Design and motion

The page emphasizes three existing projects, followed by skills, journey, and the
full gallery. Project descriptions are reused without inventing impact metrics
or personal roles. Contact actions close the page. The hero introduction avoids
asserting an unconfirmed current school status; historical timeline dates remain
unchanged pending owner confirmation.

There is no blocking preloader. Three.js and GitHub data load in parallel when the canvas approaches the viewport (600px ahead). Reduced-motion and data-saver users retain a manual load button. The local scene downloads alongside Three.js; only OrbitControls is needed afterward. Repeated scrolling reuses the initialized scene;
rendering pauses offscreen/in hidden tabs, pixel ratio is capped, and ambient
sound is disabled. Guestbook offers pause/resume and respects reduced motion.
Dialogs support keyboard activation, Escape, focus trapping and focus restoration.

Security references used:
https://supabase.com/docs/guides/database/postgres/row-level-security
https://vercel.com/docs/headers/request-headers

## Troubleshooting production HTTP 503

`RATE_LIMIT_SECRET` must be populated in Vercel → Settings → Environment Variables
for Production, alongside `APINEX_API_KEY` and `SUPABASE_SERVICE_ROLE_KEY`, followed
by a redeployment. The locally generated `.env.credentials.local` is ignored by
Git and deployment; copy only its RATE_LIMIT_SECRET value into Vercel. It is not
loaded automatically by PHP.

If the POST response has `code: configuration_missing`, inspect Vercel Function
Logs for the missing variable names (values are never logged). If its code is
`rate_limit_unavailable`, check that migration 001 has created
`consume_public_rate_limit`, and that the service role key belongs to this
Supabase project. Do not disable the limiter to resolve a configuration error.

## Contribution Orbit

The GitHub scene lives in `github-scene.js`: an instanced calendar city with
a layered deck, orbital tracks, a ringed planet, and a waving astronaut.
Camera presets, pause, and a keyboard date slider expose the actual API data.
Missing calendar days stay empty. Rendering stops offscreen and in hidden tabs;
reduced-motion renders a complete static scene with usable camera controls.
Closing sections use compact spacing, including empty testimonials/guestbook.

The runtime is vendored under `assets/three-r128` with its MIT license. The
profile universe mounts as soon as these same-origin assets load, independent
of the GitHub response. Successful GitHub data is cached per browser session
for five minutes; failures retain profile exploration and expose a retry button.
Planet information comes from public page content. Orbit mode rotates the camera;
dragging pauses automatic orbit until Orbit/reset is selected again.
