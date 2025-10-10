const express = require('express');
const router = express.Router();
const authController = require('../controllers/authController');
const { validateRequiredFields } = require('../utils/validators');

// Registro de usuario
router.post('/register', validateRequiredFields(['name', 'email', 'password']), authController.register);

// Login de usuario
router.post('/login', validateRequiredFields(['email', 'password']), authController.login);

module.exports = router;
