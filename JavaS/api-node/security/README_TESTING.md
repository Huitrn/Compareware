# 🎯 Guía de Testing Manual SQL Injection - Compareware

## 🚀 Inicio Rápido (5 minutos)

### ✅ Paso 1: Preparar el Entorno
```powershell
# 1. Abrir PowerShell en la carpeta del proyecto
cd "d:\Repositorio\Bdd Compareware\JavaS\api-node"

# 2. Iniciar el servidor (en una terminal)
npm start

# 3. Abrir otra PowerShell para testing
cd security
```

### ✅ Paso 2: Ejecutar Test Rápido
```powershell
# Opción A: Test automático rápido (más fácil)
.\QuickTest.ps1

# Opción B: Test interactivo completo (más detallado)  
.\InteractiveTesting.ps1

# Opción C: Comandos manuales individuales (más control)
# Ver sección "Testing Manual" abajo
```

### ✅ Paso 3: Interpretar Resultados

| Resultado | Significado | Acción |
|-----------|-------------|---------|
| ✅ **BLOQUEADO (403)** | Tu WAF funciona correctamente | ¡Excelente! |
| 🚨 **VULNERABLE** | Se detectó una falla de seguridad | Revisar urgente |
| ⚠️ **REVISAR** | Respuesta anómala | Investigar |

---

## 📋 Métodos de Testing Disponibles

### 🎮 Método 1: Test Automático Rápido
**⏱️ Tiempo: 2-3 minutos**
```powershell
.\QuickTest.ps1
```
**Incluye:**
- ✅ Tests básicos (comillas, comentarios)
- ✅ Union-based injection  
- ✅ Boolean blind injection
- ✅ Authentication bypass
- ✅ Time-based injection
- ✅ Bypass techniques

### 🎯 Método 2: Test Interactivo Completo  
**⏱️ Tiempo: 5-15 minutos**
```powershell
.\InteractiveTesting.ps1
```
**Características:**
- 📊 Menu interactivo
- 📝 Logging detallado  
- 📈 Estadísticas en tiempo real
- 🎓 Explicaciones educativas

### 🔧 Método 3: Testing Manual Individual
**⏱️ Tiempo: Variable**
- Control total sobre cada test
- Ideal para aprender técnicas específicas
- Ver sección "Comandos Manuales" abajo

---

## 💻 Comandos Manuales de Testing

### 🔍 1. Tests Básicos de Detección

```powershell
# Test de comilla simple (más común)
Invoke-WebRequest "http://localhost:3000/api/users/1'"

# Test de comentario SQL
Invoke-WebRequest "http://localhost:3000/api/users/1--"

# Test de punto y coma  
Invoke-WebRequest "http://localhost:3000/api/users/1;"
```

**¿Qué buscar?**
- ✅ Código 403 = WAF bloqueó
- 🚨 Errores de MySQL/PostgreSQL = Vulnerable
- ⚠️ Comportamiento extraño = Investigar

### 🔗 2. Union-Based SQL Injection

```powershell
# UNION básico
Invoke-WebRequest "http://localhost:3000/api/users?id=1' UNION SELECT 1,2,3--"

# UNION con NULL (más probable que funcione)
Invoke-WebRequest "http://localhost:3000/api/users?id=1' UNION SELECT null,null,null--"

# Intentar extraer información del sistema
Invoke-WebRequest "http://localhost:3000/api/users?id=1' UNION SELECT database(),user(),version()--"
```

**¿Qué buscar?**
- 🚨 Datos adicionales en la respuesta
- 🚨 Información de base de datos
- ✅ Error 403 o bloqueo

### 🔍 3. Boolean-Based Blind SQL Injection

```powershell
# Condición TRUE (debería devolver datos)
Invoke-WebRequest "http://localhost:3000/api/users?id=1' AND 1=1--"

# Condición FALSE (no debería devolver datos)  
Invoke-WebRequest "http://localhost:3000/api/users?id=1' AND 1=2--"

# OR TRUE (debería devolver datos)
Invoke-WebRequest "http://localhost:3000/api/users?id=1' OR 1=1--"
```

**¿Qué buscar?**
- 🚨 Respuestas diferentes entre TRUE/FALSE
- 🚨 La condición TRUE devuelve datos
- ✅ Ambas son bloqueadas igual

### ⏱️ 4. Time-Based Blind SQL Injection

```powershell
# MySQL SLEEP (medir tiempo)
Measure-Command { Invoke-WebRequest "http://localhost:3000/api/users?id=1' AND SLEEP(5)--" -TimeoutSec 10 }

# PostgreSQL SLEEP
Measure-Command { Invoke-WebRequest "http://localhost:3000/api/users?id=1'; SELECT PG_SLEEP(5)--" -TimeoutSec 10 }
```

**¿Qué buscar?**
- 🚨 Delay de exactamente 5 segundos = Vulnerable
- 🚨 Timeout del request = Posible vulnerable
- ✅ Respuesta normal rápida = Seguro

### 🚪 5. Authentication Bypass

```powershell
# Admin bypass básico
$body = '{"username":"admin'\''--","password":"anything"}'
Invoke-WebRequest -Uri "http://localhost:3000/api/auth/login" -Method POST -Body $body -ContentType "application/json"

# OR 1=1 bypass
$body = '{"username":"admin'\'' OR 1=1--","password":""}'  
Invoke-WebRequest -Uri "http://localhost:3000/api/auth/login" -Method POST -Body $body -ContentType "application/json"

# Always true condition
$body = '{"username":"anything'\'' OR '\''a'\''='\''a","password":"test"}'
Invoke-WebRequest -Uri "http://localhost:3000/api/auth/login" -Method POST -Body $body -ContentType "application/json"
```

**¿Qué buscar?**
- 🚨 `"success": true` en respuesta = CRÍTICO
- 🚨 Token o datos de sesión = CRÍTICO  
- ✅ Error 403 o login fallido = Seguro

### 🎭 6. Técnicas de Bypass

```powershell
# URL Encoding bypass
Invoke-WebRequest "http://localhost:3000/api/users?id=1%27%20OR%201%3D1--"

# Comment-based bypass  
Invoke-WebRequest "http://localhost:3000/api/users?id=1' UN/**/ION SE/**/LECT 1,2,3--"

# Case variation bypass
Invoke-WebRequest "http://localhost:3000/api/users?id=1' UnIoN sElEcT 1,2,3--"

# MySQL version comments
Invoke-WebRequest "http://localhost:3000/api/users?id=1' /*!UNION*/ /*!SELECT*/ 1,2,3--"
```

**¿Qué buscar?**
- 🚨 Algún bypass pasa el WAF = Mejorar filtros
- ✅ Todos son bloqueados = Excelente

---

## 📊 Monitoreo y Análisis

### 🔍 Ver Logs en Tiempo Real
```powershell
# Logs de seguridad
Get-Content -Path "..\logs\security_$(Get-Date -Format 'yyyy-MM-dd').log" -Wait -Tail 10

# Filtrar solo ataques SQL
Get-Content -Path "..\logs\security_$(Get-Date -Format 'yyyy-MM-dd').log" | Select-String "SQL_INJECTION"
```

### 📈 Status del Sistema
```powershell
# Ver estado general de seguridad
Invoke-WebRequest -Uri "http://localhost:3000/security/status" | ConvertFrom-Json | ConvertTo-Json -Depth 5

# Estadísticas rápidas  
(Invoke-WebRequest -Uri "http://localhost:3000/security/status").Content | ConvertFrom-Json | Select-Object -ExpandProperty data | Select-Object -ExpandProperty waf
```

### 🔎 Análisis de Logs
```powershell
# Contar ataques bloqueados hoy
$today = Get-Date -Format "yyyy-MM-dd"
(Get-Content "..\logs\security_$today.log" | Select-String "SQL_INJECTION_BLOCKED").Count

# Ver IPs atacantes
Get-Content "..\logs\security_$today.log" | ConvertFrom-Json | Where-Object {$_.eventType -eq "SQL_INJECTION_BLOCKED"} | Select-Object ip | Group-Object ip | Sort-Object Count -Descending
```

---

## 🎓 Ejercicios Prácticos

### 📝 Ejercicio 1: Identificación Básica (5 min)
1. Ejecuta `.\QuickTest.ps1`
2. Identifica cuántos tests fueron bloqueados
3. ¿Alguno fue marcado como vulnerable?
4. Revisa los logs generados

### 📝 Ejercicio 2: Testing Manual (10 min)  
1. Ejecuta manualmente 5 payloads diferentes
2. Documenta las respuestas HTTP
3. Mide el tiempo de respuesta de cada uno
4. Compara con las respuestas normales

### 📝 Ejercicio 3: Análisis de Bypass (15 min)
1. Prueba diferentes técnicas de encoding
2. Experimenta con comentarios SQL
3. Intenta variaciones de case
4. Documenta cuáles (si alguna) pasan el WAF

### 📝 Ejercicio 4: Monitoreo de Logs (10 min)
1. Inicia monitoreo de logs en tiempo real
2. Ejecuta algunos ataques
3. Observa cómo aparecen en los logs
4. Analiza la información capturada

---

## 🔧 Troubleshooting

### ❌ Problema: "No se puede conectar al servidor"
```powershell
# Verificar si el servidor está ejecutándose
Test-NetConnection -ComputerName localhost -Port 3000

# Si no responde, iniciar el servidor
cd ..\
npm start
```

### ❌ Problema: "Todos los tests pasan (200 OK)"
**Posibles causas:**
1. WAF no está activo
2. Configuración incorrecta
3. Endpoints no existen

**Solución:**
```powershell
# Verificar status del WAF
Invoke-WebRequest "http://localhost:3000/security/status" | ConvertFrom-Json
```

### ❌ Problema: "Scripts de PowerShell bloqueados"
```powershell
# Cambiar política de ejecución (temporalmente)
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser

# Después del testing, restaurar (opcional)
Set-ExecutionPolicy -ExecutionPolicy Restricted -Scope CurrentUser
```

### ❌ Problema: "Errores de timeout constantes"
```powershell
# Aumentar timeout en comandos manuales
Invoke-WebRequest -Uri "URL" -TimeoutSec 30
```

---

## 📚 Recursos Educativos

### 🎯 Para Aprender Más:
- **OWASP Top 10**: https://owasp.org/www-project-top-ten/
- **SQL Injection Cheat Sheet**: https://portswigger.net/web-security/sql-injection/cheat-sheet  
- **DVWA**: http://www.dvwa.co.uk/ (para practicar en entorno controlado)

### 📖 Documentación del Proyecto:
- `SQL_INJECTION_GUIDE.md` - Guía completa teórica
- `MANUAL_TESTING_GUIDE.md` - Guía detallada de testing manual
- `TESTING_COMMANDS.md` - Comandos para diferentes plataformas

### 🛠️ Herramientas Profesionales:
- **Burp Suite** (gratis/comercial)
- **OWASP ZAP** (gratuito)  
- **SQLMap** (línea de comandos, gratuito)

---

## ⚠️ Recordatorios Importantes

### 🔒 Uso Ético
- ✅ **SÍ**: Testear tu propio sistema
- ✅ **SÍ**: Aprender sobre seguridad
- ❌ **NO**: Atacar sistemas de terceros
- ❌ **NO**: Uso malicioso de conocimientos

### 📋 Buenas Prácticas
1. **Siempre documenta** tus hallazgos
2. **Testea regularmente** (al menos mensualmente)  
3. **Mantén logs** de todos los tests
4. **Actualiza protecciones** basándote en resultados
5. **Reporta vulnerabilidades** encontradas al equipo

### 🎓 Para Tu Proyecto Escolar
- Documenta el proceso de testing
- Explica qué vulnerabilidades buscaste
- Muestra cómo tu sistema las previene  
- Incluye capturas de pantalla de tests
- Demuestra comprensión de técnicas ofensivas y defensivas

---

## 🆘 Ayuda y Soporte

¿Tienes dudas? ¿Encontraste algo inesperado?

1. **Revisa los logs** primero
2. **Consulta la documentación** en `/security/`
3. **Ejecuta tests de diagnóstico**:
   ```powershell
   Invoke-WebRequest "http://localhost:3000/security/status"
   ```
4. **Verifica configuración** del WAF y validadores

---

**¡Ahora tienes todas las herramientas para ser un experto en testing de SQL injection!** 🛡️

¿Listo para probar la seguridad de tu sistema? ¡Comienza con `.\QuickTest.ps1`!