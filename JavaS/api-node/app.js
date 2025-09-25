

require('dotenv').config();
const express = require('express');
const cors = require('cors');
const bodyParser = require('body-parser');
const { Pool } = require('pg');

const app = express();
app.use(cors());
app.use(bodyParser.json());

console.log('DB_PASSWORD:', process.env.DB_PASSWORD);
const pool = new Pool({
  host: process.env.DB_HOST,
  port: process.env.DB_PORT,
  database: process.env.DB_DATABASE,
  user: process.env.DB_USERNAME,
  password: process.env.DB_PASSWORD,
});

// Obtener todas las marcas (GET)
app.get('/api/marcas', async (req, res) => {
  try {
    const result = await pool.query('SELECT * FROM marcas');
    res.json(result.rows);
  } catch (err) {
    console.error('Error al obtener marcas:', err);
    res.status(500).json({ error: 'Error al obtener marcas.', detalle: err.message });
  }
});

// Crear una marca (POST)
app.post('/api/marcas', async (req, res) => {
  const { nombre } = req.body;
  try {
    const result = await pool.query(
      'INSERT INTO marcas (nombre, created_at, updated_at) VALUES ($1, NOW(), NOW()) RETURNING *',
      [nombre]
    );
    res.status(201).json(result.rows[0]);
  } catch (err) {
    console.error('Error al crear marca:', err);
    res.status(500).json({ error: 'Error al crear marca.', detalle: err.message });
  }
});

// Actualizar una marca (PUT)
app.put('/api/marcas/:id', async (req, res) => {
  const { id } = req.params;
  const { nombre } = req.body;
  try {
    const result = await pool.query(
      'UPDATE marcas SET nombre=$1, updated_at=NOW() WHERE id=$2 RETURNING *',
      [nombre, id]
    );
    res.json(result.rows[0]);
  } catch (err) {
    console.error('Error al actualizar marca:', err);
    res.status(500).json({ error: 'Error al actualizar marca.', detalle: err.message });
  }
});

// Eliminar una marca (DELETE)
app.delete('/api/marcas/:id', async (req, res) => {
  const { id } = req.params;
  try {
    await pool.query('DELETE FROM marcas WHERE id=$1', [id]);
    res.json({ message: 'Marca eliminada' });
  } catch (err) {
    console.error('Error al eliminar marca:', err);
    res.status(500).json({ error: 'Error al eliminar marca.', detalle: err.message });
  }
});



// Obtener todos los periféricos (GET)
app.get('/api/perifericos', async (req, res) => {
  try {
    const result = await pool.query('SELECT * FROM perifericos');
    res.json(result.rows);
  } catch (err) {
    console.error('Error al obtener periféricos:', err);
    res.status(500).json({ error: 'Error al obtener periféricos.', detalle: err.message });
  }
});

// Crear un periférico (POST)
app.post('/api/perifericos', async (req, res) => {
  const { nombre, modelo, precio, tipo_conectividad, marca_id, categoria_id } = req.body;
  try {
    const result = await pool.query(
      'INSERT INTO perifericos (nombre, modelo, precio, tipo_conectividad, marca_id, categoria_id) VALUES ($1, $2, $3, $4, $5, $6) RETURNING *',
      [nombre, modelo, precio, tipo_conectividad, marca_id, categoria_id]
    );
    res.status(201).json(result.rows[0]);
  } catch (err) {
    console.error('Error al crear periférico:', err);
    res.status(500).json({ error: 'Error al crear periférico.', detalle: err.message });
  }
});

// Actualizar un periférico (PUT)
app.put('/api/perifericos/:id', async (req, res) => {
  const { id } = req.params;
  const { nombre, modelo, precio, tipo_conectividad, marca_id, categoria_id } = req.body;
  try {
    const result = await pool.query(
      'UPDATE perifericos SET nombre=$1, modelo=$2, precio=$3, tipo_conectividad=$4, marca_id=$5, categoria_id=$6 WHERE id=$7 RETURNING *',
      [nombre, modelo, precio, tipo_conectividad, marca_id, categoria_id, id]
    );
    res.json(result.rows[0]);
  } catch (err) {
    res.status(500).json({ error: 'Error al actualizar periférico.' });
  }
});

// Eliminar un periférico (DELETE)
app.delete('/api/perifericos/:id', async (req, res) => {
  const { id } = req.params;
  try {
    await pool.query('DELETE FROM perifericos WHERE id=$1', [id]);
    res.json({ message: 'Periférico eliminado' });
  } catch (err) {
    res.status(500).json({ error: 'Error al eliminar periférico.' });
  }
});

// Registro
app.post('/api/register', async (req, res) => {
  const { name, email, password, role } = req.body;
  try {
    const userExists = await pool.query('SELECT * FROM users WHERE email = $1', [email]);
    if (userExists.rows.length > 0) {
      return res.status(400).json({ error: 'El correo ya está registrado.' });
    }
    await pool.query(
      'INSERT INTO users (name, email, password, role, created_at) VALUES ($1, $2, $3, $4, NOW())',
      [name, email, password, role || 'user']
    );
    res.status(201).json({ message: 'Usuario registrado correctamente.' });
  } catch (err) {
    res.status(500).json({ error: 'Error en el servidor.' });
  }
});

// Login
app.post('/api/login', (req, res) => {
  const { email, password } = req.body;
  const user = users.find(u => u.email === email && u.password === password);
  if (!user) {
    return res.status(401).json({ error: 'Credenciales incorrectas.' });
  }
  // Aquí podrías generar un token JWT
  res.json({ message: 'Login exitoso', user });
});

const PORT = process.env.PORT || 4000;
app.listen(PORT, () => console.log(`API Node.js corriendo en http://localhost:${PORT}`));
