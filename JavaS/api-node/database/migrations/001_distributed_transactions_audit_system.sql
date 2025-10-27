-- =====================================================
-- MIGRACIÓN: Transacciones Distribuidas y Sistema de Auditoría
-- Versión: 1.0.0
-- Fecha: 2025-10-20
-- =====================================================

-- Tabla para Productos (si no existe)
CREATE TABLE IF NOT EXISTS products (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INTEGER DEFAULT 0,
    reserved_quantity INTEGER DEFAULT 0,
    category_id INTEGER,
    sku VARCHAR(100) UNIQUE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    deleted_at TIMESTAMP NULL
);

-- Tabla para Órdenes/Pedidos
CREATE TABLE IF NOT EXISTS orders (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'PENDING' CHECK (status IN ('PENDING', 'PROCESSING', 'SHIPPED', 'DELIVERED', 'CANCELLED')),
    shipping_address TEXT,
    billing_address TEXT,
    payment_method VARCHAR(50),
    payment_id VARCHAR(255),
    notes TEXT,
    cancellation_reason TEXT,
    cancelled_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
);

-- Tabla para Items de Órdenes
CREATE TABLE IF NOT EXISTS order_items (
    id SERIAL PRIMARY KEY,
    order_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL CHECK (quantity > 0),
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
);

-- Tabla principal de Auditoría y Logs
CREATE TABLE IF NOT EXISTS audit_logs (
    id SERIAL PRIMARY KEY,
    transaction_id VARCHAR(255) NOT NULL,
    user_id INTEGER NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INTEGER NULL,
    old_values JSONB NULL,
    new_values JSONB NULL,
    ip_address INET,
    user_agent TEXT,
    operations_count INTEGER DEFAULT 1,
    start_time TIMESTAMP NOT NULL,
    end_time TIMESTAMP NOT NULL,
    duration_ms INTEGER DEFAULT 0,
    operations_detail JSONB NULL,
    status VARCHAR(20) DEFAULT 'SUCCESS' CHECK (status IN ('SUCCESS', 'ERROR', 'PENDING')),
    error_message TEXT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Tabla para Transacciones Activas (opcional, para monitoreo en tiempo real)
CREATE TABLE IF NOT EXISTS active_transactions (
    id SERIAL PRIMARY KEY,
    transaction_id VARCHAR(255) UNIQUE NOT NULL,
    user_id INTEGER NULL,
    status VARCHAR(20) DEFAULT 'ACTIVE' CHECK (status IN ('ACTIVE', 'COMMITTED', 'ROLLED_BACK')),
    operations_count INTEGER DEFAULT 0,
    start_time TIMESTAMP DEFAULT NOW(),
    end_time TIMESTAMP NULL,
    last_operation VARCHAR(255),
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- =====================================================
-- ÍNDICES PARA OPTIMIZACIÓN DE RENDIMIENTO
-- =====================================================

-- Índices para orders
CREATE INDEX IF NOT EXISTS idx_orders_user_id ON orders(user_id);
CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(status);
CREATE INDEX IF NOT EXISTS idx_orders_created_at ON orders(created_at);
CREATE INDEX IF NOT EXISTS idx_orders_status_created ON orders(status, created_at);

-- Índices para order_items
CREATE INDEX IF NOT EXISTS idx_order_items_order_id ON order_items(order_id);
CREATE INDEX IF NOT EXISTS idx_order_items_product_id ON order_items(product_id);

-- Índices para products
CREATE INDEX IF NOT EXISTS idx_products_active ON products(is_active) WHERE is_active = TRUE;
CREATE INDEX IF NOT EXISTS idx_products_stock ON products(stock_quantity);
CREATE INDEX IF NOT EXISTS idx_products_sku ON products(sku) WHERE sku IS NOT NULL;

-- Índices para audit_logs
CREATE INDEX IF NOT EXISTS idx_audit_logs_transaction_id ON audit_logs(transaction_id);
CREATE INDEX IF NOT EXISTS idx_audit_logs_user_id ON audit_logs(user_id);
CREATE INDEX IF NOT EXISTS idx_audit_logs_action ON audit_logs(action);
CREATE INDEX IF NOT EXISTS idx_audit_logs_entity ON audit_logs(entity_type, entity_id);
CREATE INDEX IF NOT EXISTS idx_audit_logs_created_at ON audit_logs(created_at);
CREATE INDEX IF NOT EXISTS idx_audit_logs_status ON audit_logs(status);
CREATE INDEX IF NOT EXISTS idx_audit_logs_action_created ON audit_logs(action, created_at);

-- Índices para active_transactions
CREATE INDEX IF NOT EXISTS idx_active_transactions_status ON active_transactions(status);
CREATE INDEX IF NOT EXISTS idx_active_transactions_start_time ON active_transactions(start_time);

-- =====================================================
-- TRIGGERS PARA ACTUALIZACIÓN AUTOMÁTICA
-- =====================================================

-- Función para actualizar updated_at
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Triggers para actualizar updated_at
DROP TRIGGER IF EXISTS update_products_updated_at ON products;
CREATE TRIGGER update_products_updated_at 
    BEFORE UPDATE ON products 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS update_orders_updated_at ON orders;
CREATE TRIGGER update_orders_updated_at 
    BEFORE UPDATE ON orders 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS update_active_transactions_updated_at ON active_transactions;
CREATE TRIGGER update_active_transactions_updated_at 
    BEFORE UPDATE ON active_transactions 
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- =====================================================
-- DATOS DE EJEMPLO PARA TESTING
-- =====================================================

-- Insertar algunos productos de ejemplo
INSERT INTO products (name, description, price, stock_quantity, sku) VALUES
('Teclado Mecánico RGB', 'Teclado mecánico para gaming con iluminación RGB', 129.99, 50, 'KBD-RGB-001'),
('Mouse Gaming', 'Mouse óptico para gaming de alta precisión', 79.99, 75, 'MSE-GAM-001'),
('Monitor 27" 4K', 'Monitor 4K UHD de 27 pulgadas', 399.99, 25, 'MON-4K-027'),
('Auriculares Bluetooth', 'Auriculares inalámbricos con cancelación de ruido', 199.99, 40, 'AUD-BT-001'),
('Webcam HD', 'Cámara web Full HD para streaming y videollamadas', 89.99, 60, 'WEB-HD-001')
ON CONFLICT (sku) DO NOTHING;

-- =====================================================
-- VISTAS ÚTILES PARA REPORTING
-- =====================================================

-- Vista para resumen de órdenes
CREATE OR REPLACE VIEW order_summary AS
SELECT 
    o.id,
    o.user_id,
    u.name as user_name,
    u.email as user_email,
    o.status,
    o.total_amount,
    COUNT(oi.id) as items_count,
    SUM(oi.quantity) as total_items,
    o.created_at,
    o.updated_at
FROM orders o
LEFT JOIN users u ON o.user_id = u.id
LEFT JOIN order_items oi ON o.id = oi.order_id
GROUP BY o.id, u.id;

-- Vista para estadísticas de auditoría
CREATE OR REPLACE VIEW audit_statistics AS
SELECT 
    DATE(created_at) as date,
    action,
    entity_type,
    status,
    COUNT(*) as count,
    AVG(duration_ms) as avg_duration,
    MIN(duration_ms) as min_duration,
    MAX(duration_ms) as max_duration
FROM audit_logs
WHERE created_at >= CURRENT_DATE - INTERVAL '30 days'
GROUP BY DATE(created_at), action, entity_type, status
ORDER BY date DESC, count DESC;

-- Vista para transacciones fallidas recientes
CREATE OR REPLACE VIEW recent_failed_transactions AS
SELECT 
    transaction_id,
    user_id,
    action,
    entity_type,
    error_message,
    created_at,
    duration_ms
FROM audit_logs
WHERE status = 'ERROR' 
  AND created_at >= NOW() - INTERVAL '24 hours'
ORDER BY created_at DESC;

-- =====================================================
-- FUNCIONES ÚTILES
-- =====================================================

-- Función para obtener estadísticas de stock
CREATE OR REPLACE FUNCTION get_stock_statistics()
RETURNS TABLE (
    total_products BIGINT,
    low_stock_products BIGINT,
    out_of_stock_products BIGINT,
    reserved_stock_total BIGINT
) AS $$
BEGIN
    RETURN QUERY
    SELECT 
        COUNT(*) as total_products,
        COUNT(*) FILTER (WHERE stock_quantity <= 10 AND stock_quantity > 0) as low_stock_products,
        COUNT(*) FILTER (WHERE stock_quantity = 0) as out_of_stock_products,
        COALESCE(SUM(reserved_quantity), 0) as reserved_stock_total
    FROM products 
    WHERE is_active = TRUE;
END;
$$ LANGUAGE plpgsql;

-- Función para limpiar logs antiguos
CREATE OR REPLACE FUNCTION clean_old_audit_logs(days_to_keep INTEGER DEFAULT 90)
RETURNS INTEGER AS $$
DECLARE
    deleted_count INTEGER;
BEGIN
    DELETE FROM audit_logs 
    WHERE created_at < NOW() - INTERVAL '1 day' * days_to_keep;
    
    GET DIAGNOSTICS deleted_count = ROW_COUNT;
    
    -- Log de la limpieza
    INSERT INTO audit_logs (
        transaction_id, 
        action, 
        entity_type, 
        start_time, 
        end_time, 
        operations_detail
    ) VALUES (
        'cleanup_' || EXTRACT(EPOCH FROM NOW()),
        'AUDIT_CLEANUP',
        'SYSTEM',
        NOW(),
        NOW(),
        jsonb_build_object('deleted_count', deleted_count, 'days_to_keep', days_to_keep)
    );
    
    RETURN deleted_count;
END;
$$ LANGUAGE plpgsql;

-- =====================================================
-- COMENTARIOS DE LA TABLA
-- =====================================================

COMMENT ON TABLE orders IS 'Tabla principal de órdenes/pedidos del sistema';
COMMENT ON TABLE order_items IS 'Items individuales de cada orden';
COMMENT ON TABLE audit_logs IS 'Registro completo de auditoría y logs del sistema';
COMMENT ON TABLE active_transactions IS 'Transacciones distribuidas activas para monitoreo';
COMMENT ON TABLE products IS 'Catálogo de productos con control de stock';

-- =====================================================
-- MENSAJE DE CONFIRMACIÓN
-- =====================================================

DO $$
BEGIN
    RAISE NOTICE '✅ Migración completada exitosamente: Transacciones Distribuidas y Sistema de Auditoría';
    RAISE NOTICE '📊 Tablas creadas: orders, order_items, audit_logs, active_transactions, products';
    RAISE NOTICE '🔍 Índices optimizados para rendimiento';
    RAISE NOTICE '⚡ Triggers y funciones automáticas configuradas';
    RAISE NOTICE '📈 Vistas de reporting disponibles';
    RAISE NOTICE '🚀 Sistema listo para uso en producción';
END $$;