const { sendForbiddenError } = require('../utils/httpErrors');

const requireRole = (requiredRole) => {
  return (req, res, next) => {
    const userRole = req.user?.role || 'user';
    if (userRole !== requiredRole) {
      return sendForbiddenError(res, `Acceso restringido a usuarios con rol: ${requiredRole}`, requiredRole, userRole);
    }
    next();
  };
};

module.exports = { requireRole };
