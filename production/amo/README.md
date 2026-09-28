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
