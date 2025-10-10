const jwt = require('jsonwebtoken');
const { sendUnauthorizedError } = require('../utils/httpErrors');

const requireAuthentication = (req, res, next) => {
  const authHeader = req.headers.authorization;
  if (!authHeader) {
    return sendUnauthorizedError(res, 'Header Authorization no proporcionado', 'Bearer <token> o Basic <credentials>');
  }
  if (authHeader.startsWith('Basic ')) {
    const base64Credentials = authHeader.split(' ')[1];
    if (!base64Credentials) {
      return sendUnauthorizedError(res, 'Credenciales Basic Auth mal formadas', 'Basic <base64(user:password)>');
    }
    try {
      const credentials = Buffer.from(base64Credentials, 'base64').toString('ascii');
      const [email, password] = credentials.split(':');
      if (!email || !password) {
        return sendUnauthorizedError(res, 'Credenciales Basic Auth incompletas', 'Basic <base64(email:password)>');
      }
      req.auth = { email, password, type: 'basic' };
      next();
    } catch (error) {
      return sendUnauthorizedError(res, 'Error decodificando credenciales Basic Auth', 'Basic <base64(email:password)>');
    }
  } else if (authHeader.startsWith('Bearer ')) {
    const token = authHeader.split(' ')[1];
    if (!token) {
      return sendUnauthorizedError(res, 'Token JWT no proporcionado', 'Bearer <jwt-token>');
    }
    try {
      const decoded = jwt.verify(token, process.env.JWT_SECRET || 'default_secret');
      req.user = decoded;
      req.auth = { type: 'jwt', user: decoded };
      next();
    } catch (error) {
      if (error.name === 'TokenExpiredError') {
        return sendUnauthorizedError(res, 'Token JWT expirado', 'Bearer <valid-jwt-token>');
      } else if (error.name === 'JsonWebTokenError') {
        return sendUnauthorizedError(res, 'Token JWT inválido', 'Bearer <valid-jwt-token>');
      } else {
        return sendUnauthorizedError(res, 'Error validando token JWT', 'Bearer <valid-jwt-token>');
      }
    }
  } else {
    return sendUnauthorizedError(res, 'Tipo de autenticación no soportado', 'Bearer <token> o Basic <credentials>');
  }
};

module.exports = requireAuthentication;
