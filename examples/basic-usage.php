<?php

declare(strict_types=1);

/**
 * Example: Working with the ps_crossselling PrestaShop module.
 *
 * ps_crossselling displays "Customers who bought this also bought..." product
 * recommendations on the product detail page and shopping cart. It queries
 * historical order data to find frequently co-purchased products.
 *
 * This file documents common usage patterns.
 */

// --- The module hooks into product pages and cart pages ---
// Hooks used: displayShoppingCart, displayFooterProduct
// PrestaShop dispatches these automatically during page rendering.

// --- Querying cross-sell products programmatically ---
// Cross-sell recommendations are based on order co-occurrence.
// The SQL pattern (simplified):
//
// SELECT p.id_product, COUNT(*) as co_purchases
// FROM ps_order_detail od1
// JOIN ps_order_detail od2 ON od1.id_order = od2.id_order
//     AND od2.id_product != od1.id_product
// JOIN ps_product p ON p.id_product = od2.id_product
// WHERE od1.id_product = :currentProductId
//   AND p.active = 1
// GROUP BY p.id_product
// ORDER BY co_purchases DESC
// LIMIT 8;

// --- Back Office configuration ---
// Modules > Cross Selling:
//   - Number of products to display (default: 8)
//   - Hook placement (cart page, product page)

// --- Template override ---
// themes/{theme}/modules/ps_crossselling/views/templates/hook/ps_crossselling.tpl

// --- Hook: displayShoppingCart ---
// Renders recommendations on the cart page sidebar.

// --- Hook: displayFooterProduct ---
// Renders recommendations below the product description on product pages.
