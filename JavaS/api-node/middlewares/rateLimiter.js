const requestCounts = new Map();
const RATE_LIMIT_CONFIG = {
  windowMs: 60 * 1000,
  maxRequests: 5,
  message: 'Demasiadas peticiones desde esta IP, intenta nuevamente en 1 minuto'
};

const cleanupExpiredCounts = () => {
  const now = Date.now();
  for (const [key, data] of requestCounts.entries()) {
    if (now - data.resetTime > RATE_LIMIT_CONFIG.windowMs) {
      requestCounts.delete(key);
    }
  }
};

const rateLimiter = (routeIdentifier = 'default') => {
  return (req, res, next) => {
    const clientIP = req.ip || req.connection.remoteAddress || req.socket.remoteAddress || 'unknown';
    const key = `${clientIP}:${routeIdentifier}`;
    if (Math.random() < 0.1) cleanupExpiredCounts();
    const now = Date.now();
    const windowStart = now - RATE_LIMIT_CONFIG.windowMs;
    if (!requestCounts.has(key)) {
      requestCounts.set(key, {
        count: 0,
        resetTime: now,
        requests: []
      });
    }
    const userData = requestCounts.get(key);
    userData.requests = userData.requests.filter(timestamp => timestamp > windowStart);
    userData.count = userData.requests.length;
    if (userData.count >= RATE_LIMIT_CONFIG.maxRequests) {
      const oldestRequest = Math.min(...userData.requests);
      const resetTime = oldestRequest + RATE_LIMIT_CONFIG.windowMs;
      const retryAfter = Math.ceil((resetTime - now) / 1000);
      res.set({
        'X-RateLimit-Limit': RATE_LIMIT_CONFIG.maxRequests,
        'X-RateLimit-Remaining': 0,
        'X-RateLimit-Reset': new Date(resetTime).toISOString(),
        'Retry-After': retryAfter
      });
      return res.status(429).json({
        error: 'Rate limit exceeded',
        message: RATE_LIMIT_CONFIG.message,
        details: {
          limit: RATE_LIMIT_CONFIG.maxRequests,
          windowMs: RATE_LIMIT_CONFIG.windowMs,
          retryAfter: `${retryAfter} segundos`,
          resetTime: new Date(resetTime).toISOString()
        }
      });
    }
    userData.requests.push(now);
    next();
  };
};

module.exports = rateLimiter;
