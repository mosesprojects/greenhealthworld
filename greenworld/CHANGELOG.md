# Changelog — GreenWorld Wellness

All notable changes to this theme are documented here. Versioning: each release bumps the `Version` header in `style.css`.

## 1.6.1 — Homepage follow-ups

- **"Explore our world of wellness"** now scrolls **continuously on its own** (a seamless auto-marquee) instead of needing arrow clicks. It pauses on hover/focus and falls back to a static, no-motion layout for visitors who prefer reduced motion.
- **Removed the "Health & Wellness Journal" (Learn)** teaser section from the homepage — it was surfacing the default WordPress "Hello world!" post.
- Bumped `GREENWORLD_VERSION` to `1.6.1` (parent + child theme headers in lockstep) to cache-bust `gw-home.css` / `gw-home.js`.

## 1.6.0 — Homepage, product page & category refinements

### Homepage
- **"Explore our world of wellness"** pillars now render as a professional single-row carousel: 4 tiles per row on desktop (3 on tablet, 2 on mobile) with the rest reachable by horizontal scroll / arrow controls.
- **Featured Health & Wellness Products** scroller now fits **two products per row on mobile**, clearly visible, while remaining a one-row scroller on desktop.
- Removed the static **"Shop by person / Health for Everyone"** photo band and replaced it with **three live product shelves** — Men's Health, Women's Health and General Health — each showing ~6 products per row on desktop (2-up on mobile) with the remainder in a horizontal scroller.
- Every product row (Featured + the three category shelves) is **randomized on every page load**, so returning visitors see fresh products.

### Product page
- Fixed the oversized / empty product-image area: the gallery is now a **framed, sticky card** with the image constrained (`object-fit: contain`, capped height) and vertically aligned to the top of the summary — no more large blank column.
- Added a **"Green World" brand kicker** above the product title.
- Added an **"Order on WhatsApp"** call-to-action inside the product summary (number + message template come from the Customizer), matching the loop button.
- Retains the existing structured tabs (Description, Ingredients, How to Use, Delivery, Reviews) and related products below the fold.

### Category landing pages
- Product-category archives now show a **"Shop by need"** chip row of the category's sub-categories before the product grid (e.g. Men's Health → Men's Vitality, Prostate Wellness, …), building a stronger topical/entity landing page. Guides, related categories and an FAQ already render below the grid.

### Housekeeping
- Bumped `GREENWORLD_VERSION` to `1.6.0` to cache-bust `assets/css/main.css`, `assets/css/gw-home.css` and `assets/js/gw-home.js`.

## 1.0.0 — Initial release
Premium, classic health & wellness WooCommerce theme for Green World Health Solutions.

### Design
- New botanical-green + warm-ivory design system with brass accents (`theme.json` + `assets/css/main.css`).
- Editorial typography: Fraunces (display serif) + Inter (body), preconnected with `display=swap`.
- Restrained single hero (no random slider), generous whitespace, subtle motion, elegant product and category cards.

### Navigation & interaction
- Multi-level header: thin utility bar, main bar (logo, large AJAX search, account/wishlist/cart), and a primary nav with a data-driven **health mega menu**.
- Off-canvas mobile drawer, sticky header, mini-cart drawer, quick view, filters drawer, load-more, sticky add-to-cart, wishlist (localStorage), back-to-top, WhatsApp button and a mobile bottom navigation bar.
- All interactivity is vanilla, deferred and dependency-free for speed.

### Commerce
- Premium WooCommerce loop and single-product presentation: sale/new/out-of-stock badges, trust signals, delivery estimate, Ingredients and How-to-Use tabs, WhatsApp order button, Merchant-Center identifiers.
- Reimagined homepage: trust strip, Shop by Health Category, Featured Products, Best Sellers, Customer/Distributor join band, Wellness Collections, consultation band, Why Choose Us, Journal, health disclaimer and newsletter.

### Accounts
- Dual **Customer / Distributor** registration with a distributor role, applicant fields (phone, county, sponsor/referral), admin notification and a Users "Account type" column.

### Health services
- Online **Health Consultation** intake: consent-gated form, private `gw_consultation` post type, AJAX submission and owner notification, framed as guidance not diagnosis.
- Site-wide and per-product **Health Information Disclaimer**.

### Foundations
- PSR-4 module container, JSON-LD schema (Organization, WebSite, Store, Product, Breadcrumb) that yields to SEO plugins, OWASP security headers, Core Web Vitals optimizer, setup wizard, and starter content (pages, categories, demo products) tailored for Kenya (KES, M-Pesa, Pay on Delivery).
