# Changelog — GreenWorld Wellness

All notable changes to this theme are documented here. Versioning: each release bumps the `Version` header in `style.css`.

## v1.12.0 - Product image sized to the benchmark

- The product image now renders large and column-filling, matching the reference store: the gallery column is about 47 percent wide and the image fills its width at its natural aspect ratio, instead of the smaller padded square used in v1.10-v1.11. Tall bottle photos in particular now read much bigger.
- Products without an image keep the compact branded tile (gw-no-productimg body class), so imageless pages still avoid a large empty well.
- Added tidy thumbnail styling for products that have more than one image.
- CSS-only change; the has-image / no-image body classes from v1.10 do the switching. Version bump to 1.12.0.

## v1.11.0 - Product page: international (Tabarak-style) polish

- Add-to-cart CTA restyled to the brand gold accent (was WooCommerce's default mauve), scoped to the single-product buy row with high specificity so it wins without !important. Quantity and CTA now sit on one aligned row; the green Order on WhatsApp button sits full-width below.
- Replaced the plain 3-item guarantee list with a bordered 2x2 feature card (fast delivery, genuine product, secure checkout, easy returns) using distinct icons, matching the trust row used by international electronics stores.
- Added a You save X% pill under the price on sale items, and stronger price typography (bold, strikethrough original).
- Added a Brand: Green World line beside the product meta, and cleaner Category/SKU meta styling.
- All changes are CSS plus three small WooCommerce hooks (trust_badges rebuilt, sale_savings and brand_line added). Version bump to 1.11.0.

## v1.10.0 - Single-product layout rebuild

- Rebuilt the single-product page into a capped two-column grid (gallery left, summary right). The gallery is now a neat square frame instead of expanding to half the page, and the summary gets the extra room.
- Graceful no-image state: products without a featured image now get a gw-no-productimg body class and a compact branded tile, instead of a large empty well. (The data-URI placeholder from v1.8.1 still applies.)
- Added a clear In stock / Out of stock availability pill to the product summary.
- Converted the horizontal product tabs into stacked, collapsible sections using native details/summary (no JavaScript): Description (Overview, Key Features, Product Details, FAQs and so on), Ingredients, How to Use, Delivery and Reviews render as open-able panels, first one open. Related products follow below.
- WooCommerce.php: added product_body_class(), availability() and stacked_sections(); removed WooCommerce's default tabs output in favour of the stacked renderer.
- Version bump to 1.10.0.

## v1.9.0 — Company & policy pages filled from single-sourced Trust Center content

- About Us, Shipping & Delivery, Returns & Refunds, Privacy Policy, Contact Us and Terms & Conditions now render rich, consistent content via slug page templates (page-*.php) that call new TrustCenter::*_page() renderers. Previously these footer pages showed thin or empty WordPress content.
- TrustCenter refactored: render() intro is now parameterizable (shared by the Trust Center and the About page); delivery, business-facts and team markup extracted into reusable helpers; added about_page(), shipping_page(), returns_page(), privacy_page(), contact_page(), terms_page().
- Conservative sourcing wording: replaced "authorized Green World distributor" claims with the substantiated "supplies genuine Green World brand products, sourced through the group supply chain" across the About/sourcing copy and the homepage trust band.
- Added a Customizer-editable Terms & Conditions text area (Kenya governing law, KES pricing, no medical claims) under GreenWorld Theme > Trust Center.
- All copy stays Customizer-editable; no registration numbers, team names or approval badges were invented.

## 1.8.2 - Fix empty leading cell on category / shop / search grids

- **Products now fill 3-in-a-row from the first column.** On category, shop and search pages the product grid left an empty top-left cell (products started in column 2, e.g. 5 results showing as 2 + gap, then 3). Cause: with the product list set to `display:grid`, WooCommerce's old float-era clearfix pseudo-elements (`ul.products::before` / `::after`) are promoted to real grid items - the `::before` occupies the first cell and pushes every product across by one. Those pseudo-elements are now neutralised, so the grid packs cleanly with no gap. Homepage shelves (flex scrollers) were unaffected and are unchanged.

## 1.8.1 - Shelves scroll as one 5-up row; placeholder that always renders

- **Homepage shelves now show one row of 5 products and scroll**, instead of wrapping into a 3-up grid. The shelf scroller was being overridden by the base category grid (`.woocommerce ul.products{display:grid}` outranks `.gw-scroller .products`); a higher-specificity `.gw-scroller[data-gw-scroller]` rule now wins, forcing a single horizontal flex row (5-up desktop, 4 on laptop, 3 on tablet, 2 on mobile). Product sorting into the shelves is unchanged (fixed in 1.8.0).
- **Branded placeholder now renders everywhere.** The 1.8.0 placeholder pointed at a theme file that could 404 on the live host, so image-less products fell back to the blank WooCommerce placeholder (the white boxes / gaps). The placeholder is now an inline data-URI, so it renders regardless of file deployment or server MIME config. Products still need real featured images and prices added in WooCommerce for the best result.

## 1.8.0 - Captioned slider + delivery slide, sorted shelves, no empty cards

- **Hero slider shows captions again.** The homepage carousel was rebuilt from the caption-less banner mode back to a captioned hero: each slide shows the real product banner as a full-bleed cover background under a brand-green scrim, with the eyebrow, headline, description and call-to-action rendered as real text on top, sized to fit the hero band (no letterboxed "half" banner).
- **Delivery banner moved into the slider.** The standalone deliveries band at the bottom of the homepage was removed and folded into the carousel as a redesigned, captioned delivery slide ("Nationwide & Worldwide Delivery" - same-day Nairobi, countrywide courier, DHL worldwide, pay on delivery), instead of the busy original banner shown as-is.
- **Homepage shelves are correctly sorted.** Men's / Women's / Vitamins shelves now resolve by the title-keyword classifier first (word boundaries keep "woMEN"/"MENopause"/"MENstrual" out of the Men's shelf) and never fall back to raw product search. Category membership is still honoured as a secondary source, but filtered through the same exclude rules so a mis-categorised product cannot leak in.
- **No more empty product cards / grid gaps.** Products with no featured image now show a branded Green World placeholder instead of a blank white box, so category, shop and search grids stay even. A product with no price (e.g. "Ovary Nutrition Capsules") still needs a price added in WooCommerce - that is product data, not a theme bug.

## 1.7.0 - Shorter hero, de-polluted shelves, 3-up base grid

- **Hero height halved.** v1.6.9 made the banner a full-width 2:1 block, which is very tall on desktop. The slide is now a fixed, modest band (clamp 240-400px) with the real banner shown crisp and complete (object-fit:contain) over a blurred, darkened copy of itself that fills the frame - so it is short AND full, never letterboxed. Reverted to the raw banner images (the 2:1 composites are no longer needed).
- **Homepage shelves + category relevance fixed at the source of the bug.** A WordPress search for "Men's Wellness" also matches "woMEN", "MENopause", "MENstrual", so the Men's shelf was full of women's products. Shelves now resolve by category first, then by title keywords with word boundaries (which correctly separate men / women / vitamins), and only fall back to raw search as a last resort.
- **Category / shop / search grid is now 3-up on desktop by default** (base grid changed from 4 to 3 columns with !important), so it no longer depends on a wrapper class and can't fall back to a ragged 2-up. Mobile stays 2-up.
- **Build is now verifiable:** the running theme version is printed as an HTML comment in the page head (View Source) so you can confirm a deploy took effect.
- Note: products with a missing price/image (e.g. 'Ovary Nutrition Capsules') still render a thin card - that is a WooCommerce data fix (add a price/image or mark out-of-stock), not a theme issue.

## 1.6.9 - Full-bleed hero banners + 3-up category grid

- **Hero banners now fill the slider edge-to-edge** instead of floating letterboxed ("half"). All four banners (Weight Loss, Men's, Women's, General) were rebuilt onto a uniform 1600x800 (2:1) canvas: the real Green World banner sits crisp and centred on a soft blurred, brand-tinted backdrop, so nothing is cropped and the frame is always full. The slider CSS switched from object-fit:contain to a fixed 2:1 slide with object-fit:cover.
- **Women's slide** swapped from the awkward portrait/"THANKS" composite to the clean Women Care Gel product panel.
- **Category / shop / search pages now show a guaranteed 3 products per row** on desktop (was 4-up and could leave a ragged empty cell from WooCommerce float fallback). Cards are forced onto an equal-height CSS grid with a stable 1:1 image box, so a product with a missing or odd-sized image no longer breaks the alignment. Mobile stays 2-up.
- No change to the Featured / Men's / Women's / Vitamins shelves themselves - they were already 5-up horizontal scrollers pulling from those category searches and randomising each load (this only became visible once the fatal errors from 1.6.6/1.6.7 were fixed).

## 1.6.8 - Hotfix: hero URL sprintf crash

- **Critical fix for a fatal error on the homepage** (`ValueError: Unknown format specifier "W"` from `sprintf()` inside `get_theme_mod()`). The hero slide URLs are built with `rawurlencode()` (e.g. `?s=Men%27s%20Wellness`), and WordPress runs a theme-mod's string default through `sprintf()`, which choked on the `%` sequences.
- The encoded URL is no longer passed as the `get_theme_mod` default. Each hero slide now reads `gw_heroN_url` with an empty default and falls back to the built-in search URL in PHP, so the encoded URL never touches `sprintf()`.
- No visual change; the 4-slide banner hero and deliveries band are unchanged.

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
