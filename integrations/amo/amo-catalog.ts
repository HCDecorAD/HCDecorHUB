export const AMO_API = 'https://hc-shop-engine.huycuongonline.workers.dev';
export const AMO_STORE = 'store_amo';

export type AmoCatalogItem = {
  id: string | number;
  slug?: string;
  name: string;
  price?: number;
  sale_price?: number | null;
  image?: string | null;
  brand?: string;
  category?: string;
  available?: boolean;
};

export type AmoCatalog = { store?: string; items: AmoCatalogItem[] };

export async function fetchAmoCatalog(signal?: AbortSignal): Promise<AmoCatalog> {
  const response = await fetch(`${AMO_API}/api/catalog`, {
    method: 'GET',
    headers: { 'x-store-id': AMO_STORE },
    signal,
  });
  if (!response.ok) throw new Error(`AMO catalog HTTP ${response.status}`);
  const payload: unknown = await response.json();
  if (!payload || typeof payload !== 'object' || !('items' in payload) ||
      !Array.isArray(payload.items)) {
    throw new Error('AMO catalog: invalid items');
  }
  return payload as AmoCatalog;
}

export function resolveAmoField(
  items: AmoCatalogItem[],
  recordId: string | number,
  field: keyof AmoCatalogItem,
): string {
  const item = items.find((entry) => String(entry.id) === String(recordId) ||
    entry.slug === String(recordId));
  if (!item) throw new Error(`AMO item not found: ${recordId}`);
  const value = item[field];
  return value == null ? '' : String(value);
}
