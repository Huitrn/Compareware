const axios = require('axios');
const TTLCache = require('../utils/cache');

/**
 * PriceAggregatorService
 * - Integra precios desde múltiples marketplaces (RapidAPI): eBay, Walmart, etc.
 * - Amazon ya se consume en Laravel; opcionalmente se puede agregar aquí si se define host/base.
 * - Devuelve lista unificada con retailer, precio, moneda, url y disponibilidad.
 */
class PriceAggregatorService {
  constructor() {
    this.cache = new TTLCache(10 * 60 * 1000); // 10 minutos
    this.rapidApiKey = process.env.RAPIDAPI_KEY || '';

    // eBay (ejemplo de host configurable)
    this.ebayHost = process.env.RAPIDAPI_EBAY_HOST || '';
    this.ebayBase = this.ebayHost ? `https://${this.ebayHost}` : null;

    // Walmart
    this.walmartHost = process.env.RAPIDAPI_WALMART_HOST || '';
    this.walmartBase = this.walmartHost ? `https://${this.walmartHost}` : null;

    // Amazon opcional (si quieres centralizar aquí también)
    this.amazonHost = process.env.RAPIDAPI_AMAZON_HOST || '';
    this.amazonBase = this.amazonHost ? `https://${this.amazonHost}` : null;
  }

  async getPrices({ query, ean, upc, country = 'US', currency = 'USD', limit = 5 }) {
    const key = `prices:${ean || upc || query}:${country}:${currency}:${limit}`.toLowerCase();
    const cached = this.cache.get(key);
    if (cached) return { source: 'cache', ...cached };

    const results = [];

    // 1) eBay
    if (this.ebayBase) {
      try {
        const r = await axios.get(`${this.ebayBase}/search`, {
          params: { query, page: 1 },
          headers: { 'X-RapidAPI-Key': this.rapidApiKey, 'X-RapidAPI-Host': this.ebayHost },
          timeout: 12000,
        });
        const items = (r.data && (r.data.data || r.data.items || [])) || [];
        results.push(
          ...items.slice(0, limit).map((x) => ({
            retailer: 'eBay',
            title: x.title || x.name,
            price: x.price || x.current_price || null,
            currency: x.currency || currency,
            url: x.url || x.offer_url || x.link,
            image: x.image || (x.images && x.images[0]) || null,
            availability: x.availability || 'unknown',
          }))
        );
      } catch (_) {}
    }

    // 2) Walmart
    if (this.walmartBase) {
      try {
        const r = await axios.get(`${this.walmartBase}/search`, {
          params: { query, page: 1 },
          headers: { 'X-RapidAPI-Key': this.rapidApiKey, 'X-RapidAPI-Host': this.walmartHost },
          timeout: 12000,
        });
        const items = (r.data && (r.data.data || r.data.items || [])) || [];
        results.push(
          ...items.slice(0, limit).map((x) => ({
            retailer: 'Walmart',
            title: x.title || x.name,
            price: x.price || x.current_price || null,
            currency: x.currency || currency,
            url: x.url || x.offer_url || x.link,
            image: x.image || (x.images && x.images[0]) || null,
            availability: x.availability || 'unknown',
          }))
        );
      } catch (_) {}
    }

    // 3) Amazon (opcional aquí)
    if (this.amazonBase) {
      try {
        const r = await axios.get(`${this.amazonBase}/search`, {
          params: { query, page: 1, country },
          headers: { 'X-RapidAPI-Key': this.rapidApiKey, 'X-RapidAPI-Host': this.amazonHost },
          timeout: 12000,
        });
        const items = (r.data && r.data.data && r.data.data.products) || [];
        results.push(
          ...items.slice(0, limit).map((x) => ({
            retailer: 'Amazon',
            title: x.title,
            price: x.price ? x.price.current_price : x.product_price,
            currency: (x.price && x.price.currency) || currency,
            url: x.product_url || x.url,
            image: x.product_photo || (x.images && x.images[0]) || null,
            availability: x.is_prime ? 'prime' : 'in_stock',
          }))
        );
      } catch (_) {}
    }

    const aggregated = { success: true, items: results.slice(0, limit * 3) };
    this.cache.set(key, aggregated);
    return aggregated;
  }
}

module.exports = PriceAggregatorService;
