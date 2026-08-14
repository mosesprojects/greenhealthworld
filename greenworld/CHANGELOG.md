## v1.27.0

- Homepage performance (PageSpeed): the 10 "Shop by need" category tiles were 1024px JPGs totalling ~6.7 MB (each ~600-780 KB) but displayed at ~230-320px. Recompressed to 720px optimized progressive JPG + WebP siblings: total drops to ~427 KB via WebP (-94%) / ~710 KB JPG (-90%). `Home::cat_image()` gains a `cat_webp()` sibling and the tiles now render as `<picture>` (WebP source + JPG fallback) with `decoding="async"`.
- Fixed the desktop "spinner": the YITH Wishlist React widget fires a guest REST call to `wishlist/v1/lists` that returns 401 and stalls ~1s (longest critical-path request; also logged a console error). The theme ships its own localStorage wishlist, so `Optimizer::trim_wishlist_widget()` now dequeues YITH's wishlist frontend bundle on the homepage and shop/category/tag archives (matched by src, dequeue-only), leaving it intact on the dedicated wishlist page. Removes the stall and a chunk of render-blocking JS from those pages.

