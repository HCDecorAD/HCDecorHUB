# AMO NGUYEN storefront module

Production-oriented storefront structure. No invented products, prices, stock or brands.

Routes:
- / home
- /shoe-guide/
- /en/
- /lien-he/

Catalog slots:
- Loafers
- Oxford & Derby
- Sneakers
- Boots

Data contract:
```json
{"sku":"","name":"","category":"","price":null,"currency":"VND","stock":null,"images":[],"published":false}
```

Only records with real SKU/name/price/stock supplied by the store should be published.

## Responsive production rules
- Mobile-first touch targets >= 44px.
- 4-column style grid collapses to 2 and then 1 column.
- Navigation links resolve only to AMO-owned routes.
- Production hostname is fixed to https://amonnguyen.hcdecorhub.com.
