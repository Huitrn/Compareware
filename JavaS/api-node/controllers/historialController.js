const pool = require('../config/db');

// Obtener historial de comparaciones de un usuario, mostrando nombres de periféricos
const getHistorialComparaciones = async (req, res) => {
  try {
    const userId = req.user.id;
    const result = await pool.query(`
      SELECT ch.id, ch.fecha,
        p1.id AS periferico_1_id, p1.nombre AS periferico_1_nombre,
        p2.id AS periferico_2_id, p2.nombre AS periferico_2_nombre
      FROM comparacion_historial ch
      JOIN perifericos p1 ON ch.periferico_1 = p1.id
      JOIN perifericos p2 ON ch.periferico_2 = p2.id
      WHERE ch.user_id = $1
      ORDER BY ch.fecha DESC
    `, [userId]);
    res.json(result.rows);
  } catch (error) {
    res.status(500).json({ error: 'Error al obtener historial de comparaciones', details: error.message });
  }
};

module.exports = { getHistorialComparaciones };
// Guardar una comparación en el historial
const guardarComparacion = async (req, res) => {
  try {
    const userId = req.user.id;
    const { periferico_1, periferico_2 } = req.body;
    if (!periferico_1 || !periferico_2) {
      return res.status(400).json({ error: 'Faltan datos de periféricos para la comparación.' });
    }
    await pool.query(
      'INSERT INTO comparacion_historial (user_id, periferico_1, periferico_2, fecha) VALUES ($1, $2, $3, NOW())',
      [userId, periferico_1, periferico_2]
    );
    res.status(201).json({ message: 'Comparación guardada en el historial.' });
  } catch (error) {
    res.status(500).json({ error: 'Error al guardar comparación en el historial', details: error.message });
  }
};

module.exports = { getHistorialComparaciones, guardarComparacion };