const axios = require('axios');
const TTLCache = require('../utils/cache');

/**
 * ProductSpecsService
 * - Agrega integración con APIs de especificaciones técnicas.
 * - Fuentes sugeridas: OpenIcecat (especificaciones por EAN/UPC) y Product Details API de RapidAPI (fallback por nombre).
 * - Uso: preferir EAN/UPC -> marca/modelo -> búsqueda por nombre.
 */
class ProductSpecsService {
  constructor() {
    this.cache = new TTLCache(60 * 60 * 1000); // 1 hora

    this.rapidApiKey = process.env.RAPIDAPI_KEY || '';
    // Hosts configurables por entorno (permite intercambiar proveedor en RapidAPI)
    this.productDetailsHost = process.env.RAPIDAPI_PRODUCT_DETAILS_HOST || 'product-details-api.p.rapidapi.com';
    this.productDetailsBase = `https://${this.productDetailsHost}`;

    // OpenIcecat vía RapidAPI (si se configura)
    this.icecatHost = process.env.RAPIDAPI_ICECAT_HOST || ''; // p.ej. 'icecat1-icecat-live-product-data-v1.p.rapidapi.com'
    this.icecatBase = this.icecatHost ? `https://${this.icecatHost}` : null;
  }

  async getSpecs({ query, ean, upc, brand, category }) {
    const cacheKey = `specs:${ean || upc || query || ''}:${brand || ''}:${category || ''}`.toLowerCase();
    const cached = this.cache.get(cacheKey);
    if (cached) return { source: 'cache', ...cached };

    // 1) Intentar por EAN/UPC en Icecat (si está configurado)
    if ((ean || upc) && this.icecatBase) {
      try {
        const res = await axios.get(`${this.icecatBase}/product`, {
          params: { ean: ean || upc },
          headers: {
            'X-RapidAPI-Key': this.rapidApiKey,
            'X-RapidAPI-Host': this.icecatHost,
          },
          timeout: 15000,
        });
        if (res.status === 200 && res.data) {
          const normalized = this._normalizeIcecat(res.data);
          this.cache.set(cacheKey, { success: true, data: normalized });
          return { success: true, data: normalized, provider: 'icecat' };
        }
      } catch (e) {
        // continuar con fallback
      }
    }

    // 2) Fallback: Product Details API por nombre
    if (query) {
      try {
        const res = await axios.get(`${this.productDetailsBase}/search`, {
          params: {
            q: query,
            category: category || 'electronics',
            include_specs: true,
            language: 'en'
          },
          headers: {
            'X-RapidAPI-Key': this.rapidApiKey,
            'X-RapidAPI-Host': this.productDetailsHost,
            Accept: 'application/json'
          },
          timeout: 15000,
        });
        if (res.status === 200 && res.data) {
          const item = (res.data.results && res.data.results[0]) || null;
          const normalized = this._normalizeProductDetails(item, { query, brand, category });
          this.cache.set(cacheKey, { success: true, data: normalized });
          return { success: true, data: normalized, provider: 'product-details' };
        }
      } catch (e) {
        // continuar con mock
      }
    }

    // 3) Mock básico si todo falla
    const mock = this._mockSpecs(query || brand || 'Producto');
    this.cache.set(cacheKey, { success: true, data: mock });
    return { success: true, data: mock, provider: 'mock' };
  }

  _normalizeIcecat(raw) {
    // Normalización básica; ajustar según payload real del proveedor
    const p = raw || {};
    return {
      title: p.Title || p.productName || p.Name || 'Producto',
      brand: p.Brand || p.Manufacturer || p.BrandName || undefined,
      model: p.Model || p.MPN || p.PartNumber || undefined,
      ean: p.EAN || p.GTIN || undefined,
      upc: p.UPC || undefined,
      specs: p.Specs || p.specifications || p.Attributes || p.features || {},
      images: p.Images || p.ImageGallery || [],
      source: 'Icecat'
    };
  }

  _normalizeProductDetails(item, ctx) {
    if (!item) {
      return this._mockSpecs(ctx.query || 'Producto');
    }
    const specs = item.specifications || {};
    return {
      title: item.title || item.name || ctx.query,
      brand: specs.brand || item.brand || ctx.brand,
      model: specs.model || item.model || undefined,
      ean: specs.ean || specs.gtin || undefined,
      upc: specs.upc || undefined,
      specs: {
        general: {
          brand: specs.brand,
          model: specs.model,
          color: specs.color,
          weight: specs.weight,
          dimensions: specs.dimensions,
        },
        audio: {
          frequency_response: specs.frequency_response,
          impedance: specs.impedance,
          sensitivity: specs.sensitivity,
          driver_size: specs.driver_size,
          noise_cancellation: specs.noise_cancellation,
        },
        connectivity: {
          connection_type: specs.connection_type,
          bluetooth_version: specs.bluetooth_version,
          wireless_range: specs.wireless_range,
          cable_length: specs.cable_length,
        },
        power: {
          battery_life: specs.battery_life,
          charging_time: specs.charging_time,
          charging_port: specs.charging_port,
        },
        features: {
          microphone: specs.microphone,
          controls: specs.controls,
          compatibility: specs.compatibility,
          special_features: specs.special_features,
        },
      },
      images: item.images || [],
      source: 'Product Details API'
    };
  }

  _mockSpecs(name) {
    return {
      title: name,
      specs: {
        general: { type: 'Periférico electrónico', model: name },
      },
      images: [],
      source: 'mock'
    };
  }
}

module.exports = ProductSpecsService;
