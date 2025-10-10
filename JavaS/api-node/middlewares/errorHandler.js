const { logSecurityEvent } = require('./logging');

const globalErrorHandler = (err, req, res, next) => {
  console.error('🚨 ERROR EN EXPRESS:', err);
  logSecurityEvent('EXPRESS_ERROR', {
    ip: req.ip || 'unknown',
    route: req.path || 'unknown',
    message: 'Error en middleware de Express',
    data: {
      error: err.message,
      method: req.method,
      url: req.url,
      stack: err.stack
    }
  });

  if (!res.headersSent) {
    res.status(500).json({
      status: 'error',
      message: 'Error interno del servidor',
      error: 'Ha ocurrido un error inesperado',
      timestamp: new Date().toISOString()
    });
  }
};

module.exports = globalErrorHandler;
