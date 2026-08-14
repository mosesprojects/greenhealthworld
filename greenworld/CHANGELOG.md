## v1.29.0

- Introduced the **Green World Core** companion plugin (`greenworld-core/`) so business data (scan bookings now, and customer/distributor records + points in later phases) lives independently of the theme and survives theme updates or switches.
- **Book your scan** is now a booking form (name, phone, preferred date/time, location, note). Each booking is saved under a new "Scan Bookings" admin list AND sent automatically to staff on WhatsApp (Meta Cloud API) when configured. The scan band gracefully falls back to the previous WhatsApp button if the plugin is inactive.
- **Consultations** now also forward a copy to staff on WhatsApp on submit; the existing admin record and email notification are unchanged. Wired via a new `greenworld/consultation_submitted` action hook.

## v1.28.0

- Fixed single-product gallery horizontal overflow on Chrome / Samsung Internet (hero image spilling off the right edge on mobile). Root cause: the v1.26 CSS gallery made the hero `__image` (and its `<a>`) flex containers, and flexbox `min-width:auto` stops a flex item shrinking below its content's intrinsic width — so an 800px+ product image refused to shrink on a ~380px phone and overflowed. (Chromium browsers still showing the pre-v1.26 cached bundle, e.g. Brave, were unaffected.)
- Fix in `WooCommerce::critical_product_css()`: the hero is now block-based (`display:block; text-align:center`, inline-block centered image) instead of flex; added `min-width:0` to every gallery flex item and to all product grid cells; `box-sizing:border-box` where padding meets `width:100%`; and `overflow:hidden` on `.woocommerce-product-gallery` as a guard so nothing can ever spill outside the column again.
- Product summary column: added `min-width:0` + `overflow-wrap:break-word` so long descriptions can't clip at the right edge.

