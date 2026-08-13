# Changelog — GreenWorld Wellness

All notable changes to this theme are documented here. Versioning: each release bumps the `Version` header in `style.css`.

## 1.6.7 - Hotfix: restore Home renderer methods

- **Critical fix for a fatal error introduced in 1.6.6** (`Call to undefined method GreenWorld\Front\Home::hero_slides()`). A bad in-place edit in 1.6.6 accidentally deleted three private helper methods on the homepage renderer (`product_ids()`, `render_products()`, `section_head()`) and the `hero_slides()` method signature, which took the whole homepage down.
- All four methods are restored to their correct definitions; the 4-slide banner hero and deliveries band from 1.6.6 are unchanged and intact.
- No visual or behavioural changes beyond restoring the homepage. If you deployed 1.6.6 and saw a white screen / fatal error, deploy 1.6.7 to recover.

## 1.6.6 - Banner hero + deliveries band

- **New homepage hero: a 4-slide banner carousel** using your real marketing banners (Weight Loss Without Exercise, Men's Wellness, Women's Wellness, Premium Supplements). Each banner is shown complete (no cropping, no dark overlay), cross-fades every 6s, pauses on hover, supports swipe, and links to the matching category search. Images are bundled in the theme and can be swapped per slide in the Customizer (gw_hero1..4_image).
- **New full-width deliveries band** below the category shelves, using your 'We Deliver Internationally / DHL / Pay on Delivery Kenya' banner, linking to the shop. Image overridable via the Customizer (gw_delivery_image).
- Bundled banner assets under assets/img/slides/ (integrity-verified).
- Bumped version to `1.6.6` (parent + child).

## 1.6.5 - Homepage shelves aligned to the Wellness catalog

- **The three homepage product rows now pull from your live catalog categories: Men's Wellness, Women's Wellness, and Vitamins & Minerals** (previously Men's / Women's / General Health). Each row resolves products in this order: exact product category (incl. sub-categories) -> the on-site product search for that term (mirrors /?s=Men's%20Wellness&post_type=product) -> title-keyword match. So the rows match what your `?s=` search pages show, and stay populated as you assign products.
- Added a `products_by_search()` shelf resolver that reuses WooCommerce search, randomised on every load like the other rows.
- Each row still shows 5 fully-visible cards on desktop (from 1.6.4) with the rest in a horizontal scroller; category/search archive pages remain 3-up.
- Bumped version to `1.6.5` (parent + child).

## 1.6.4 - Category-page grid, professional filters, homepage 5-up rows

- **Category / shop pages now show 3 products per row** (was 2 with a blank column). The archive product grid uses `minmax(0,1fr)` tracks so cards shrink to fit instead of wrapping early and leaving empty space; 2-up on tablet/mobile.
- **Redesigned the filter sidebar** to an international-standard look: a sticky card with a clear "Filter products" header, pill-shaped product counts on categories, a green active state, styled price inputs with focus rings, and custom check marks for the rating/attribute options. The mobile off-canvas drawer behaviour is unchanged.
- **Homepage product rows now show exactly 5 fully-visible cards per row on desktop** (Featured plus the Men's / Women's / General shelves), with the rest reachable by horizontal scroll - no more half-cut 5th card. 3-up on tablet, 2-up on mobile. Every row is still randomized on each page load.
- **Added a one-time category-hierarchy seeder.** On the first wp-admin load after deploy it creates the Green World category tree (Vitamins & Minerals; Women's Wellness -> Menopause / Menstrual / Reproductive; Men's Wellness -> Vitality / Prostate / Reproductive; Digestive, Immune, Bone & Joint, Heart & Circulatory, Weight Management, Herbal & Natural, General Wellness). It is additive and idempotent - it never renames, deletes or reassigns, and the new categories stay hidden from menus/filters until you assign products to them.
- Bumped version to `1.6.4` (parent + child).

## 1.6.3 — Category shelves now populate reliably

- **Fixed the three homepage category rows (Men's / Women's / General Health) showing empty.** They now resolve products in this order: (1) membership of the matching product categories, including sub-categories and several alternate category names; (2) if those categories are missing or empty, a fallback that matches products by title keywords with word boundaries (so "…for Men" lands under Men's Health and "Menopause…"/"…for Women" under Women's Health). Rows still show 6/5 per row (2-up on mobile) with scroll + View all, randomized each load.
- Root cause: products were not assigned to categories named exactly "Men's Health" / "Women's Health" / "General Health" (the Shop-by-Category tiles read "Explore" with no count). Assigning products to those categories in WooCommerce makes the rows switch back to exact category membership automatically.
- Bumped version to `1.6.3` (parent + child).

## 1.6.2 — Homepage cleanup + category shelf styling

- **Removed the homepage medical-disclaimer band and the "Join our wellness list" newsletter block.** (The disclaimer still appears in the footer, and the footer promise badges/columns are unchanged.)
- **Restyled the three category shelves** (Men's / Women's / General Health) to match the reference: a bold uppercase section title with a short green underline accent and a "View all" link on the right, dropping the repeated "Shop by category" eyebrow. They remain 6/5-per-row scrollers (2-up on mobile) sitting immediately after Featured Health & Wellness Products, randomized on every load.
- Bumped version to `1.6.2` (parent + child) to cache-bust CSS/JS.

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
