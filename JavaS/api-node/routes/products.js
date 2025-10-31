const express = require('express');
const router = express.Router();

const ProductSpecsService = require('../services/ProductSpecsService');
const PriceAggregatorService = require('../services/PriceAggregatorService');

const specsService = new ProductSpecsService();
const priceService = new PriceAggregatorService();

// GET /api/products/specs?query=...&ean=...&upc=...&brand=...&category=...
router.get('/specs', async (req, res) => {
  try {
    const { query, ean, upc, brand, category } = req.query;
    if (!query && !ean && !upc) {
      return res.status(400).json({ success: false, message: 'Debe proporcionar query, ean o upc' });
    }
    const data = await specsService.getSpecs({ query, ean, upc, brand, category });
    res.json(data);
  } catch (err) {
    res.status(500).json({ success: false, message: 'Error obteniendo especificaciones', details: err.message });
  }
});

// GET /api/products/prices?query=...&country=US&currency=USD
router.get('/prices', async (req, res) => {
  try {
    const { query, ean, upc, country = 'US', currency = 'USD', limit = 5 } = req.query;
    if (!query && !ean && !upc) {
      return res.status(400).json({ success: false, message: 'Debe proporcionar query, ean o upc' });
    }
    const data = await priceService.getPrices({ query, ean, upc, country, currency, limit: Number(limit) });
    res.json(data);
  } catch (err) {
    res.status(500).json({ success: false, message: 'Error obteniendo precios', details: err.message });
  }
});

// GET /api/products/compare?product1=...&product2=...&country=US&currency=USD
router.get('/compare', async (req, res) => {
  try {
    const { product1, product2, country = 'US', currency = 'USD' } = req.query;
    if (!product1 || !product2) {
      return res.status(400).json({ success: false, message: 'Debe proporcionar product1 y product2' });
    }

    const [specs1, specs2, prices1, prices2] = await Promise.all([
      specsService.getSpecs({ query: product1 }),
      specsService.getSpecs({ query: product2 }),
      priceService.getPrices({ query: product1, country, currency, limit: 5 }),
      priceService.getPrices({ query: product2, country, currency, limit: 5 }),
    ]);

    res.json({
      success: true,
      comparison: {
        product1: { name: product1, specs: specs1.data, prices: prices1.items },
        product2: { name: product2, specs: specs2.data, prices: prices2.items },
      }
    });
  } catch (err) {
    res.status(500).json({ success: false, message: 'Error comparando productos', details: err.message });
  }
});

module.exports = router;
