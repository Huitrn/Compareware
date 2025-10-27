-- Tabla para historial de comparaciones de periféricos por usuario
CREATE TABLE IF NOT EXISTS comparacion_historial (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    periferico_1 INTEGER NOT NULL REFERENCES perifericos(id) ON DELETE CASCADE,
    periferico_2 INTEGER NOT NULL REFERENCES perifericos(id) ON DELETE CASCADE,
    fecha TIMESTAMP NOT NULL DEFAULT NOW()
);

-- Índice para búsquedas rápidas por usuario
CREATE INDEX IF NOT EXISTS idx_comparacion_historial_user_id ON comparacion_historial(user_id);
