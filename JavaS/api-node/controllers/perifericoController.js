const pool = require('../config/db');

const list = async (req, res) => {
  try {
    const result = await pool.query('SELECT * FROM perifericos');
    res.json(result.rows);
  } catch (error) {
    res.status(500).json({ error: 'Error al listar periféricos', details: error.message });
  }
};

const create = async (req, res) => {
  try {
    const { nombre, marca, categoria } = req.body;
    const result = await pool.query(
      'INSERT INTO perifericos (nombre, marca, categoria) VALUES ($1, $2, $3) RETURNING *',
      [nombre, marca, categoria]
    );
    res.status(201).json(result.rows[0]);
  } catch (error) {
    res.status(500).json({ error: 'Error al crear periférico', details: error.message });
  }
};

const update = async (req, res) => {
  try {
    const { id } = req.params;
    const { nombre, marca, categoria } = req.body;
    const result = await pool.query(
      'UPDATE perifericos SET nombre = $1, marca = $2, categoria = $3 WHERE id = $4 RETURNING *',
      [nombre, marca, categoria, id]
    );
    res.json(result.rows[0]);
  } catch (error) {
    res.status(500).json({ error: 'Error al actualizar periférico', details: error.message });
  }
};

const remove = async (req, res) => {
  try {
    const { id } = req.params;
    await pool.query('DELETE FROM perifericos WHERE id = $1', [id]);
    res.json({ success: true });
  } catch (error) {
    res.status(500).json({ error: 'Error al eliminar periférico', details: error.message });
  }
};

module.exports = { list, create, update, remove };
