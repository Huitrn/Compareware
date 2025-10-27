const express = require('express');
const router = express.Router();
const authController = require('../controllers/authController');
const { validateRequiredFields } = require('../utils/validators');
const { strictSQLProtection } = require('../middlewares/sqlSecurityMiddleware');

// Registro de usuario
router.post('/register', 
  strictSQLProtection,  // 🛡️ Protección SQL estricta
  validateRequiredFields(['name', 'email', 'password']), 
  authController.register
);

// Login de usuario
router.post('/login', 
  strictSQLProtection,  // 🛡️ Protección SQL estricta
  validateRequiredFields(['email', 'password']), 
  authController.login
);

module.exports = router;
