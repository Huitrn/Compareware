const HTTP_ERRORS = {
  400: { title: 'Bad Request', description: 'La petición contiene datos inválidos o mal formados' },
  401: { title: 'Unauthorized', description: 'Se requiere autenticación para acceder a este recurso' },
  403: { title: 'Forbidden', description: 'No tienes permisos suficientes para acceder a este recurso' },
  404: { title: 'Not Found', description: 'El recurso solicitado no fue encontrado' },
  409: { title: 'Conflict', description: 'La petición entra en conflicto con el estado actual del recurso' },
  500: { title: 'Internal Server Error', description: 'Error interno del servidor' }
};

const sendBadRequestError = (res, message = 'Datos inválidos', details = {}, validationErrors = []) => {
  res.setHeader('X-Error-Type', 'VALIDATION_ERROR');
  res.setHeader('X-Error-Code', '400');
  res.setHeader('X-Validation-Failed', 'true');
  const errorResponse = {
    status: 'error',
    error: {
      code: 400,
      type: HTTP_ERRORS[400].title,
      message: message,
      description: HTTP_ERRORS[400].description
    },
    details: details,
    validation: {
      failed: true,
      errors: validationErrors,
      count: validationErrors.length
    },
    timestamp: new Date().toISOString(),
    requestId: res.getHeader('X-Request-ID')
  };
  return res.status(400).json(errorResponse);
};

const sendUnauthorizedError = (res, message = 'Autenticación requerida', authType = 'Bearer Token o Basic Auth') => {
  res.setHeader('X-Error-Type', 'AUTHENTICATION_ERROR');
  res.setHeader('X-Error-Code', '401');
  res.setHeader('WWW-Authenticate', authType);
  res.setHeader('X-Auth-Required', 'true');
  const errorResponse = {
    status: 'error',
    error: {
      code: 401,
      type: HTTP_ERRORS[401].title,
      message: message,
      description: HTTP_ERRORS[401].description
    },
    authentication: {
      required: true,
      type: authType,
      hint: 'Proporciona credenciales válidas en el header Authorization'
    },
    timestamp: new Date().toISOString(),
    requestId: res.getHeader('X-Request-ID')
  };
  return res.status(401).json(errorResponse);
};

const sendForbiddenError = (res, message = 'Acceso denegado', requiredRole = null, userRole = null) => {
  res.setHeader('X-Error-Type', 'AUTHORIZATION_ERROR');
  res.setHeader('X-Error-Code', '403');
  res.setHeader('X-Access-Denied', 'true');
  if (requiredRole) res.setHeader('X-Required-Role', requiredRole);
  if (userRole) res.setHeader('X-User-Role', userRole);
  const errorResponse = {
    status: 'error',
    error: {
      code: 403,
      type: HTTP_ERRORS[403].title,
      message: message,
      description: HTTP_ERRORS[403].description
    },
    authorization: {
      denied: true,
      requiredRole: requiredRole,
      currentRole: userRole,
      hint: requiredRole ? `Se requiere rol: ${requiredRole}` : 'Permisos insuficientes'
    },
    timestamp: new Date().toISOString(),
    requestId: res.getHeader('X-Request-ID')
  };
  return res.status(403).json(errorResponse);
};

module.exports = { sendBadRequestError, sendUnauthorizedError, sendForbiddenError };
