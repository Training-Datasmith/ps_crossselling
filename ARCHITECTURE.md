# Architecture: ps_crossselling

## Purpose

A PrestaShop module that displays cross-selling product recommendations on the shopping
cart page, based on products that other customers have purchased along with the cart's
current items.

## Directory Structure

```
ps_crossselling.php   # Main module class (WidgetInterface)
views/templates/       # Smarty/Twig templates for product list
translations/          # Translation files
tests/                 # PHPStan and unit tests
```

## Key Design Decisions

Uses SQL queries against the order history tables to identify products commonly purchased
alongside items in the current cart. Results are filtered by current category restrictions
and product availability. Configurable number of recommendations.

## Extension Points

Configure the number of displayed products in the module back-office settings.
Override template in theme for custom product card layout.
