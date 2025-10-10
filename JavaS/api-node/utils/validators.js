const isValidEmail = (email) => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
};

const isValidId = (id) => {
  return id && !isNaN(id) && parseInt(id) > 0;
};

const validateRequiredFields = (requiredFields) => {
  return (req, res, next) => {
    const validationErrors = [];
    
    requiredFields.forEach(field => {
      if (!req.body[field]) {
        validationErrors.push({
          field: field,
          message: `El campo '${field}' es requerido`,
          received: req.body[field] || null
        });
      }
    });
    
    if (req.body.email && !isValidEmail(req.body.email)) {
      validationErrors.push({
        field: 'email',
        message: 'El formato del email es inválido',
        received: req.body.email
      });
    }
    
    if (req.body.password && req.body.password.length < 6) {
      validationErrors.push({
        field: 'password',
        message: 'La contraseña debe tener al menos 6 caracteres',
        received: `${req.body.password.length} caracteres`
      });
    }
    
    if (validationErrors.length > 0) {
      return res.status(400).json({
        status: 'error',
        message: 'Datos de entrada inválidos',
        validationErrors: validationErrors
      });
    }
    
    next();
  };
};

module.exports = { isValidEmail, isValidId, validateRequiredFields };
