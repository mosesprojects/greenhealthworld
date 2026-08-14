# Green World Core

Companion plugin for the GreenWorld theme. It holds the business logic that must
survive a theme change: WhatsApp notifications, scan bookings, and (in later
phases) the customer and distributor dashboards with points.

Keeping this in a plugin — not the theme — means your bookings, customer records,
distributor status, and points ledger are **not** lost if the theme is updated,
switched, or rebuilt.

## What ships in v0.1.0 (Phase 1)

- **WhatsApp Cloud API sender** with a settings screen (Settings -> Green World).
- **Scan bookings**: `[gw_scan_form]` shortcode + a "Scan Bookings" admin list.
  Each booking is saved as a private record **and** sent to staff on WhatsApp.
- **Consultation bridge**: when the theme's consultation form is submitted, a copy
  is forwarded to staff on WhatsApp. The existing admin record + email are kept.

Later phases (2-4) add the customer dashboard (orders, refill/adjustment requests,
progress check-ins, staff messaging), distributor onboarding + admin activation,
and batch allocation + points.

## Install / deploy

This plugin lives in the repository at `greenworld-core/`, next to the `greenworld`
and `greenworld-child` theme folders.

1. Copy (or symlink) the `greenworld-core/` folder into `wp-content/plugins/` on the
   server, so the path is `wp-content/plugins/greenworld-core/greenworld-core.php`.
2. In wp-admin go to **Plugins** and activate **Green World Core**.
3. Flush permalinks once (Settings -> Permalinks -> Save) if the "Scan Bookings"
   menu does not appear immediately.

The plugin degrades gracefully: with it deactivated, the theme falls back to the
old "Book your scan" WhatsApp button, and consultations still save + email as before.

## Configure WhatsApp (Meta Cloud API)

You need a free Meta WhatsApp Cloud API app. High level:

1. Go to <https://developers.facebook.com/> -> create an app -> add the
   **WhatsApp** product.
2. In **WhatsApp -> API Setup**, note the **Phone number ID** and generate an
   **access token**. For production, create a **System User** token so it does not
   expire (temporary tokens last ~24 hours and are for testing only).
3. Add and verify the business phone number you want messages sent **from**.
4. In wp-admin -> **Settings -> Green World**, fill in:
   - **Enable WhatsApp alerts**: ticked
   - **Access token**: the (permanent/system-user) token
   - **Phone number ID**: from API Setup
   - **API version**: leave as `v21.0` unless Meta tells you otherwise
   - **Staff recipient numbers**: the staff WhatsApp number(s) that should receive
     bookings/consultations, full international format, digits only
     (e.g. `254723579873`), comma-separated for more than one.
5. Save. The **Last WhatsApp error** box on that screen shows the most recent API
   error (blank means the last send was accepted).

### Important: the 24-hour window

Meta only lets a business send **free-form text** to a number that has messaged the
business number within the last 24 hours. For staff alerts, the simplest reliable
setup is: **have each staff recipient send any message to the business WhatsApp
number** (this opens a rolling 24-hour window as long as they stay in contact).

To message staff reliably outside that window you must use an **approved message
template**. Create one in Meta (WhatsApp Manager -> Message templates), then enter
its **Template name** and **language** on the settings screen. (Template send is
scaffolded for a later release; plain text covers the in-window case now.)

## Data model (for maintainers)

- Scan bookings: post type `gw_scan` (private), meta keys `_gw_s_name`,
  `_gw_s_phone`, `_gw_s_date`, `_gw_s_time`, `_gw_s_location`, `_gw_s_note`.
- Settings: option `gwc_settings` (array). Last WhatsApp error: option
  `gwc_wa_last_error`.
- Theme hook consumed: `do_action( 'greenworld/consultation_submitted', $data )`.
- Hook fired: `do_action( 'greenworld/scan_booked', $post_id )`.
