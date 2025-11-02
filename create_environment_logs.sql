-- Crear tabla environment_logs
CREATE TABLE IF NOT EXISTS environment_logs (
    id SERIAL PRIMARY KEY,
    environment VARCHAR(50) NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    data JSONB,
    ip_address VARCHAR(45),
    user_agent TEXT,
    session_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Crear índices para mejor performance
CREATE INDEX IF NOT EXISTS idx_environment_logs_environment ON environment_logs(environment);
CREATE INDEX IF NOT EXISTS idx_environment_logs_created_at ON environment_logs(created_at);
CREATE INDEX IF NOT EXISTS idx_environment_logs_action ON environment_logs(action);