const APP_CONFIG = require('../config/appConfig');

const addCustomHeaders = (req, res, next) => {
  res.setHeader('X-App-Version', APP_CONFIG.version);
  res.setHeader('X-App-Name', APP_CONFIG.name);
  res.setHeader('X-API-Version', APP_CONFIG.apiVersion);
  res.setHeader('X-Build-Date', APP_CONFIG.buildDate);
  res.setHeader('X-Environment', APP_CONFIG.environment);
  res.setHeader('X-Author', APP_CONFIG.author);
  res.setHeader('X-Content-Type-Options', 'nosniff');
  res.setHeader('X-Frame-Options', 'DENY');
  res.setHeader('X-XSS-Protection', '1; mode=block');
  res.setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
  res.setHeader('X-Response-Time', Date.now());
  res.setHeader('X-Request-ID', `req_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`);
  res.setHeader('X-Powered-By', 'Node.js + Express + PostgreSQL');
  res.setHeader('X-Server-Instance', `compareware-api-${process.pid}`);
  next();
};

module.exports = addCustomHeaders;
