
// server.js
const express = require('express');
const { Pool } = require('pg');
const cors = require('cors');

const app = express();
const port = 3000;

app.use(cors());
app.use(express.json());

// Configura tu conexión PostgreSQL aquí
const pool = new Pool({
  connectionString: 'postgres://postgres:[YOUR-PASSWORD]@db.nhjzwkjjmjqwphpobkrr.supabase.co:5432/postgres',
  ssl: {
    rejectUnauthorized: false
  }
});

// Ruta de prueba para verificar la conexión
app.get('/test-db', async (req, res) => {
  try {
    const result = await pool.query('SELECT NOW()');
    res.json({ time: result.rows[0] });
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: 'Database connection failed' });
  }
});

app.listen(port, () => {
  console.log(`Server running on http://localhost:${port}`);
});
