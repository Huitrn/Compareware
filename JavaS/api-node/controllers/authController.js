const pool = require('../config/db');
const jwt = require('jsonwebtoken');
const bcrypt = require('bcryptjs');

const register = async (req, res) => {
  const { name, email, password } = req.body;
  try {
    // Verificar si el usuario ya existe
    const existingUser = await pool.query('SELECT id FROM users WHERE email = $1', [email]);
    if (existingUser.rows.length > 0) {
      return res.status(400).json({ 
        error: 'El usuario ya existe', 
        message: 'Ya existe una cuenta con este correo electrónico' 
      });
    }

    const hashedPassword = await bcrypt.hash(password, 10);
    const result = await pool.query(
      'INSERT INTO users (name, email, password, role, created_at, updated_at) VALUES ($1, $2, $3, $4, NOW(), NOW()) RETURNING id, name, email, role, created_at',
      [name, email, hashedPassword, 'user']
    );
    
    res.status(201).json({ 
      message: '¡Usuario registrado exitosamente!', 
      user: result.rows[0] 
    });
  } catch (error) {
    console.error('Error en registro:', error);
    if (error.code === '23505') { // Código de PostgreSQL para violación de constraint único
      return res.status(400).json({ 
        error: 'El correo electrónico ya está registrado' 
      });
    }
    res.status(500).json({ 
      error: 'Error interno del servidor', 
      message: 'No se pudo completar el registro' 
    });
  }
};

const login = async (req, res) => {
  const { email, password } = req.body;
  try {
    const result = await pool.query('SELECT * FROM users WHERE email = $1', [email]);
    const user = result.rows[0];
    if (!user) return res.status(401).json({ error: 'Usuario no encontrado' });
    const valid = await bcrypt.compare(password, user.password);
    if (!valid) return res.status(401).json({ error: 'Contraseña incorrecta' });
    const token = jwt.sign({ id: user.id, email: user.email, role: user.role }, process.env.JWT_SECRET || 'default_secret', { expiresIn: '1d' });
    res.json({ token, user });
  } catch (error) {
    res.status(500).json({ error: 'Error al iniciar sesión', details: error.message });
  }
};

module.exports = { register, login };
