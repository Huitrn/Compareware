# 🛡️ Compareware Security Testing Commands

## Comandos Rápidos para Testing de Seguridad

### Preparación
```bash
# Instalar dependencias de seguridad
cd JavaS/api-node/security
npm install

# Asegurar que el directorio de logs existe
mkdir -p ../logs
```

### Tests de Penetración

```bash
# Test completo de penetración SQL injection
npm run security:test

# Test rápido de un payload específico
npm run security:test:quick

# Test via API (servidor debe estar ejecutándose)
npm run security:pentest

# Test rápido via API
npm run security:pentest:quick
```

### Monitoreo en Tiempo Real

```bash
# Ver logs de seguridad en tiempo real
npm run security:logs

# Ver estadísticas actuales
npm run security:stats

# Ver status del sistema de seguridad
npm run security:status
```

### Validación Manual

```bash
# Validar un input específico
npm run security:validate "' OR 1=1--"

# Ejemplo con diferentes tipos de ataques
npm run security:validate "admin'--"
npm run security:validate "1 UNION SELECT * FROM users--"
npm run security:validate "'; DROP TABLE users--"
```

### Testing con cURL

```bash
# Test de endpoint con payload malicioso
curl -X GET "http://localhost:3000/api/users?id=' OR 1=1--"

# Test de POST con SQL injection
curl -X POST \
  -H "Content-Type: application/json" \
  -d '{"username": "admin'\''--", "password": "anything"}' \
  http://localhost:3000/api/auth/login

# Test con User-Agent malicioso
curl -X GET \
  -H "User-Agent: sqlmap/1.4.7" \
  http://localhost:3000/api/users

# Test de bypass con encoding
curl -X GET "http://localhost:3000/api/users?search=%27%20OR%201%3D1--"
```

### Análisis de Logs

```bash
# Buscar intentos de SQL injection
grep "SQL_INJECTION" logs/security_$(date +%Y-%m-%d).log

# Contar ataques por IP
grep "SQL_INJECTION" logs/security_$(date +%Y-%m-%d).log | \
  jq -r '.ip' | sort | uniq -c | sort -nr

# Ver top de rutas atacadas
grep "SQL_INJECTION" logs/security_$(date +%Y-%m-%d).log | \
  jq -r '.route' | sort | uniq -c | sort -nr

# Analizar tipos de ataques
grep "SQL_INJECTION" logs/security_$(date +%Y-%m-%d).log | \
  jq -r '.details.detectedAttacks[].type' | sort | uniq -c
```

### Testing Avanzado con SQLMap

```bash
# SOLO EN TU ENTORNO DE DESARROLLO
# Nunca ejecutar contra sistemas de terceros

# Test básico
sqlmap -u "http://localhost:3000/api/users?id=1" --batch

# Test con cookies de sesión
sqlmap -u "http://localhost:3000/api/users?id=1" \
       --cookie="session=abc123" --batch

# Test POST data
sqlmap -u "http://localhost:3000/api/auth/login" \
       --data="username=admin&password=test" \
       --batch

# Test con diferentes técnicas
sqlmap -u "http://localhost:3000/api/users?id=1" \
       --technique=BEUSTQ --batch --level=3 --risk=3

# Test de bypass de WAF
sqlmap -u "http://localhost:3000/api/users?id=1" \
       --tamper=space2comment,charencode --batch
```

### Verificación de Protecciones

```bash
# Verificar que el WAF está bloqueando
curl -v -X GET "http://localhost:3000/api/users?id=' OR 1=1--" | \
  grep -E "(HTTP|blocked|403)"

# Test de rate limiting
for i in {1..70}; do
  curl -s http://localhost:3000/api/users?id=$i > /dev/null
  echo "Request $i sent"
done

# Verificar headers de seguridad
curl -I http://localhost:3000/api/users | \
  grep -E "(X-Content-Type-Options|X-Frame-Options|X-XSS-Protection)"
```

### Scripts de Automatización

```bash
# Script para probar múltiples payloads
cat > test_payloads.sh << 'EOF'
#!/bin/bash
payloads=(
  "' OR 1=1--"
  "' UNION SELECT 1,2,3--"
  "admin'--"
  "'; DROP TABLE users--"
  "1; WAITFOR DELAY '00:00:05'--"
)

for payload in "${payloads[@]}"; do
  echo "Testing: $payload"
  curl -s "http://localhost:3000/api/users?id=$payload" | \
    jq '.success // "BLOCKED"'
  sleep 1
done
EOF

chmod +x test_payloads.sh
./test_payloads.sh
```

### Análisis de Performance

```bash
# Medir tiempo de respuesta durante ataques
ab -n 100 -c 10 "http://localhost:3000/api/users?id=1"

# Test con payloads maliciosos (medir impacto del WAF)
ab -n 50 -c 5 "http://localhost:3000/api/users?id=%27%20OR%201%3D1--"

# Monitorear memoria y CPU durante tests
top -p $(pgrep -f "node.*app.js") &
npm run security:test
```

### Reportes y Análisis

```bash
# Generar reporte de seguridad diario
node -e "
const fs = require('fs');
const logs = fs.readFileSync('logs/security_$(date +%Y-%m-%d).log', 'utf8')
  .split('\n')
  .filter(line => line.trim())
  .map(line => JSON.parse(line));

const attacks = logs.filter(log => log.eventType?.includes('SQL_INJECTION'));
console.log('=== REPORTE DIARIO DE SEGURIDAD ===');
console.log('Total de eventos:', logs.length);
console.log('Ataques SQL detectados:', attacks.length);
console.log('IPs únicas atacantes:', [...new Set(attacks.map(a => a.ip))].length);
"

# Exportar logs para análisis externo
jq -s '.' logs/security_$(date +%Y-%m-%d).log > security_report_$(date +%Y-%m-%d).json
```

### Limpieza y Mantenimiento

```bash
# Limpiar logs antiguos (más de 7 días)
find logs/ -name "*.log" -mtime +7 -delete

# Comprimir logs antiguos
find logs/ -name "*.log" -mtime +1 -exec gzip {} \;

# Reset de estadísticas (reiniciar contadores)
curl -X POST http://localhost:3000/security/reset-stats
```

## 🎓 Ejercicios Prácticos

### Ejercicio 1: Test Básico
1. Inicia tu servidor
2. Ejecuta `npm run security:test:quick`
3. Verifica que los ataques sean bloqueados
4. Revisa los logs generados

### Ejercicio 2: Análisis de Logs
1. Ejecuta varios tests de penetración
2. Analiza los patrones en los logs
3. Identifica las técnicas de ataque más comunes
4. Verifica que el WAF esté funcionando

### Ejercicio 3: Tuning del WAF
1. Modifica la configuración del WAF
2. Prueba diferentes niveles de restricción
3. Mide el impacto en performance
4. Encuentra el balance entre seguridad y usabilidad

### Ejercicio 4: Testing de Bypass
1. Intenta técnicas de bypass conocidas
2. Verifica si alguna pasa el WAF
3. Ajusta las reglas de detección
4. Documenta los hallazgos

## ⚠️ Importante

- **NUNCA** uses estas herramientas contra sistemas que no te pertenezcan
- Siempre ejecuta tests en entornos controlados
- Mantén logs de todos los tests realizados
- Revisa regularmente la efectividad de las protecciones
- Actualiza las reglas de seguridad basándote en nuevas amenazas