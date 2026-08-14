## v1.28.0

- Fixed single-product gallery horizontal overflow on Chrome / Samsung Internet (hero image spilling off the right edge on mobile). Root cause: the v1.26 CSS gallery made the hero `__image` (and its `<a>`) flex containers, and flexbox `min-width:auto` stops a flex item shrinking below its content's intrinsic width — so an 800px+ product image refused to shrink on a ~380px phone and overflowed. (Chromium browsers still showing the pre-v1.26 cached bundle, e.g. Brave, were unaffected.)
- Fix in `WooCommerce::critical_product_css()`: the hero is now block-based (`display:block; text-align:center`, inline-block centered image) instead of flex; added `min-width:0` to every gallery flex item and to all product grid cells; `box-sizing:border-box` where padding meets `width:100%`; and `overflow:hidden` on `.woocommerce-product-gallery` as a guard so nothing can ever spill outside the column again.
- Product summary column: added `min-width:0` + `overflow-wrap:break-word` so long descriptions can't clip at the right edge.

