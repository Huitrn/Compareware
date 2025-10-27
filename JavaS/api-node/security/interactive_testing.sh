#!/bin/bash

# 🎯 Script Interactivo de Testing Manual SQL Injection
# Proyecto: Compareware - Desarrollo Backend

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Configuración
BASE_URL="http://localhost:3000"
LOGFILE="manual_test_results_$(date +%Y%m%d_%H%M%S).log"

# Banner
print_banner() {
    echo -e "${CYAN}"
    echo "╔══════════════════════════════════════════════════════════════════╗"
    echo "║                    🛡️  COMPAREWARE SQL INJECTION                 ║"
    echo "║                        MANUAL TESTING SUITE                     ║"
    echo "║                                                                  ║"
    echo "║  ⚠️  SOLO PARA TESTING DE TU PROPIO SISTEMA - USO EDUCATIVO     ║"
    echo "╚══════════════════════════════════════════════════════════════════╝"
    echo -e "${NC}"
}

# Log function
log_result() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOGFILE"
    echo -e "$2$1${NC}"
}

# Test function con análisis detallado
test_payload() {
    local description=$1
    local url=$2
    local method=${3:-"GET"}
    local data=${4:-""}
    local expected=${5:-"blocked"}
    
    echo -e "\n${BLUE}🧪 Testing: $description${NC}"
    echo -e "${PURPLE}URL: $url${NC}"
    
    if [ "$method" = "GET" ]; then
        response=$(curl -s -w "HTTPSTATUS:%{http_code};TIME:%{time_total}" "$url")
    else
        response=$(curl -s -w "HTTPSTATUS:%{http_code};TIME:%{time_total}" \
                       -X "$method" -H "Content-Type: application/json" -d "$data" "$url")
    fi
    
    http_code=$(echo "$response" | grep -o "HTTPSTATUS:[0-9]*" | cut -d: -f2)
    time_total=$(echo "$response" | grep -o "TIME:[0-9.]*" | cut -d: -f2)
    body=$(echo "$response" | sed 's/HTTPSTATUS:[0-9]*;TIME:[0-9.]*//')
    
    # Análisis de la respuesta
    if [ "$http_code" = "403" ] || echo "$body" | grep -q "blocked\|ACCESS_DENIED\|security"; then
        log_result "✅ BLOCKED - $description (HTTP: $http_code, Time: ${time_total}s)" "$GREEN"
        return 0
    elif [ "$http_code" = "200" ]; then
        if echo "$body" | grep -q "mysql_\|PostgreSQL\|ORA-\|SQL Server\|syntax error"; then
            log_result "🚨 VULNERABLE! Database error exposed - $description" "$RED"
            echo -e "${RED}Error details: ${body:0:200}...${NC}"
            return 2
        elif echo "$body" | grep -q "users\|admin\|database\|table"; then
            log_result "⚠️  SUSPICIOUS RESPONSE - Possible data leakage - $description" "$YELLOW"
            return 1
        else
            log_result "⚠️  PASSED - No obvious vulnerability signs - $description (Time: ${time_total}s)" "$YELLOW"
            return 1
        fi
    else
        log_result "ℹ️  OTHER RESPONSE - HTTP $http_code - $description (Time: ${time_total}s)" "$CYAN"
        return 0
    fi
}

# Verificar si el servidor está ejecutándose
check_server() {
    echo -e "${BLUE}🔍 Verificando conexión al servidor...${NC}"
    
    if curl -s --connect-timeout 5 "$BASE_URL" > /dev/null 2>&1; then
        echo -e "${GREEN}✅ Servidor accesible en $BASE_URL${NC}"
        return 0
    else
        echo -e "${RED}❌ Servidor no accesible en $BASE_URL${NC}"
        echo -e "${YELLOW}Por favor, inicia tu servidor primero:${NC}"
        echo -e "${CYAN}  cd JavaS/api-node && npm start${NC}"
        exit 1
    fi
}

# Menu principal
show_menu() {
    echo -e "\n${CYAN}📋 MENU DE TESTING${NC}"
    echo "1. 🎯 Test Básico de Vulnerabilidades"
    echo "2. 🔍 Test de Boolean-Based Blind SQL Injection"  
    echo "3. 🔗 Test de Union-Based SQL Injection"
    echo "4. ⏱️  Test de Time-Based Blind SQL Injection"
    echo "5. 💥 Test de Error-Based SQL Injection"
    echo "6. 🚪 Test de Authentication Bypass"
    echo "7. 🎭 Test de Bypass Techniques"
    echo "8. 🚀 Test Completo (Todos los anteriores)"
    echo "9. 📊 Ver Resultados y Estadísticas"
    echo "0. ❌ Salir"
    echo ""
    read -p "Selecciona una opción (0-9): " choice
}

# Test 1: Básico
test_basic() {
    echo -e "\n${YELLOW}🎯 INICIANDO TESTS BÁSICOS${NC}"
    echo "Estos tests verifican respuestas a caracteres SQL comunes"
    
    test_payload "Comilla simple" "$BASE_URL/api/users/1'"
    test_payload "Punto y coma" "$BASE_URL/api/users/1;"
    test_payload "Comentario SQL" "$BASE_URL/api/users/1--"
    test_payload "Comentario de bloque" "$BASE_URL/api/users/1/*"
    
    echo -e "\n${GREEN}✅ Tests básicos completados${NC}"
}

# Test 2: Boolean-Based Blind
test_boolean_blind() {
    echo -e "\n${YELLOW}🔍 INICIANDO BOOLEAN-BASED BLIND SQL INJECTION TESTS${NC}"
    echo "Estos tests verifican si diferentes condiciones TRUE/FALSE producen respuestas diferentes"
    
    test_payload "Condición TRUE (1=1)" "$BASE_URL/api/users?id=1' AND 1=1--"
    test_payload "Condición FALSE (1=2)" "$BASE_URL/api/users?id=1' AND 1=2--"
    test_payload "OR TRUE" "$BASE_URL/api/users?id=1' OR 1=1--"
    test_payload "Verificación longitud BD" "$BASE_URL/api/users?id=1' AND LENGTH(DATABASE())>5--"
    test_payload "Extracción carácter" "$BASE_URL/api/users?id=1' AND SUBSTRING(DATABASE(),1,1)='c'--"
    
    echo -e "\n${GREEN}✅ Tests boolean blind completados${NC}"
}

# Test 3: Union-Based
test_union_based() {
    echo -e "\n${YELLOW}🔗 INICIANDO UNION-BASED SQL INJECTION TESTS${NC}"
    echo "Estos tests intentan extraer datos usando UNION SELECT"
    
    test_payload "UNION básico" "$BASE_URL/api/users?id=1' UNION SELECT 1,2,3--"
    test_payload "UNION con NULL" "$BASE_URL/api/users?id=1' UNION SELECT null,null,null--"
    test_payload "Extraer info del sistema" "$BASE_URL/api/users?id=1' UNION SELECT database(),user(),version()--"
    test_payload "Listar tablas" "$BASE_URL/api/users?id=1' UNION SELECT table_name,null,null FROM information_schema.tables--"
    test_payload "Listar columnas" "$BASE_URL/api/users?id=1' UNION SELECT column_name,data_type,null FROM information_schema.columns WHERE table_name='users'--"
    
    echo -e "\n${GREEN}✅ Tests union-based completados${NC}"
}

# Test 4: Time-Based Blind
test_time_based() {
    echo -e "\n${YELLOW}⏱️  INICIANDO TIME-BASED BLIND SQL INJECTION TESTS${NC}"
    echo "Estos tests verifican demoras anómalas que indican ejecución de código SQL"
    
    echo -e "${CYAN}Nota: Cada test puede tardar hasta 5 segundos si es vulnerable${NC}"
    
    test_payload "MySQL SLEEP" "$BASE_URL/api/users?id=1' AND SLEEP(3)--"
    test_payload "PostgreSQL SLEEP" "$BASE_URL/api/users?id=1'; SELECT PG_SLEEP(3)--"
    test_payload "SQL Server WAITFOR" "$BASE_URL/api/users?id=1'; WAITFOR DELAY '00:00:03'--"
    test_payload "Conditional time delay" "$BASE_URL/api/users?id=1' AND IF(1=1,SLEEP(2),0)--"
    
    echo -e "\n${GREEN}✅ Tests time-based completados${NC}"
}

# Test 5: Error-Based
test_error_based() {
    echo -e "\n${YELLOW}💥 INICIANDO ERROR-BASED SQL INJECTION TESTS${NC}"
    echo "Estos tests intentan provocar errores que revelen información"
    
    test_payload "Error de sintaxis básico" "$BASE_URL/api/users?id=1''"
    test_payload "MySQL EXTRACTVALUE" "$BASE_URL/api/users?id=1' AND EXTRACTVALUE(1,CONCAT(0x7e,(SELECT database()),0x7e))--"
    test_payload "MySQL UPDATEXML" "$BASE_URL/api/users?id=1' AND UPDATEXML(1,CONCAT(0x7e,(SELECT user()),0x7e),1)--"
    test_payload "PostgreSQL CAST error" "$BASE_URL/api/users?id=1' AND CAST((SELECT version()) AS int)--"
    test_payload "División por cero" "$BASE_URL/api/users?id=1' AND 1/0--"
    
    echo -e "\n${GREEN}✅ Tests error-based completados${NC}"
}

# Test 6: Authentication Bypass
test_auth_bypass() {
    echo -e "\n${YELLOW}🚪 INICIANDO AUTHENTICATION BYPASS TESTS${NC}"
    echo "Estos tests intentan evadir la autenticación usando SQL injection"
    
    test_payload "Admin bypass básico" "$BASE_URL/api/auth/login" "POST" '{"username":"admin'"'"'--","password":"anything"}'
    test_payload "OR 1=1 bypass" "$BASE_URL/api/auth/login" "POST" '{"username":"admin'"'"' OR 1=1--","password":""}'
    test_payload "OR TRUE bypass" "$BASE_URL/api/auth/login" "POST" '{"username":"admin'"'"' OR '"'"'1'"'"'='"'"'1'"'"'","password":"test"}'
    test_payload "Comentario bypass" "$BASE_URL/api/auth/login" "POST" '{"username":"admin'"'"'/*","password":"anything"}'
    test_payload "Always true condition" "$BASE_URL/api/auth/login" "POST" '{"username":"anything'"'"' OR '"'"'a'"'"'='"'"'a","password":"test"}'
    
    echo -e "\n${GREEN}✅ Tests authentication bypass completados${NC}"
}

# Test 7: Bypass Techniques
test_bypass_techniques() {
    echo -e "\n${YELLOW}🎭 INICIANDO BYPASS TECHNIQUES TESTS${NC}"
    echo "Estos tests verifican técnicas avanzadas para evadir filtros"
    
    # URL Encoding
    test_payload "URL Encoding" "$BASE_URL/api/users?id=1%27%20OR%201%3D1--"
    
    # Comment-based bypass
    test_payload "Comment bypass UNION" "$BASE_URL/api/users?id=1' UN/**/ION SE/**/LECT 1,2,3--"
    
    # MySQL version comments
    test_payload "MySQL version comment" "$BASE_URL/api/users?id=1' /*!UNION*/ /*!SELECT*/ 1,2,3--"
    
    # Case variation
    test_payload "Case variation" "$BASE_URL/api/users?id=1' UnIoN sElEcT 1,2,3--"
    
    # Alternative whitespace
    test_payload "Tab separators" "$BASE_URL/api/users?id=1'%09UNION%0ASELECT%091,2,3--"
    
    # Concatenation
    test_payload "String concatenation" "$BASE_URL/api/users?id=1' OR 'a'='a"
    
    echo -e "\n${GREEN}✅ Tests bypass techniques completados${NC}"
}

# Test completo
run_full_test() {
    echo -e "\n${PURPLE}🚀 EJECUTANDO SUITE COMPLETA DE TESTS${NC}"
    echo "Esto ejecutará todos los tests disponibles..."
    
    test_basic
    test_boolean_blind  
    test_union_based
    test_time_based
    test_error_based
    test_auth_bypass
    test_bypass_techniques
    
    show_statistics
}

# Mostrar estadísticas
show_statistics() {
    echo -e "\n${CYAN}📊 ESTADÍSTICAS DE TESTING${NC}"
    echo "═══════════════════════════════════════"
    
    if [ -f "$LOGFILE" ]; then
        total_tests=$(grep -c "Testing:" "$LOGFILE" 2>/dev/null || echo "0")
        blocked_tests=$(grep -c "✅ BLOCKED" "$LOGFILE" 2>/dev/null || echo "0")
        vulnerable_tests=$(grep -c "🚨 VULNERABLE" "$LOGFILE" 2>/dev/null || echo "0")
        suspicious_tests=$(grep -c "⚠️" "$LOGFILE" 2>/dev/null || echo "0")
        
        echo -e "📝 Total de tests ejecutados: ${BLUE}$total_tests${NC}"
        echo -e "🛡️  Tests bloqueados (seguro): ${GREEN}$blocked_tests${NC}"
        echo -e "🚨 Vulnerabilidades encontradas: ${RED}$vulnerable_tests${NC}"
        echo -e "⚠️  Respuestas sospechosas: ${YELLOW}$suspicious_tests${NC}"
        
        if [ "$total_tests" -gt 0 ]; then
            security_score=$(( (blocked_tests * 100) / total_tests ))
            echo -e "📊 Score de seguridad: ${CYAN}${security_score}%${NC}"
            
            if [ "$security_score" -ge 90 ]; then
                echo -e "🎯 Nivel de seguridad: ${GREEN}EXCELENTE${NC}"
            elif [ "$security_score" -ge 70 ]; then
                echo -e "🎯 Nivel de seguridad: ${BLUE}BUENO${NC}"
            elif [ "$security_score" -ge 50 ]; then
                echo -e "🎯 Nivel de seguridad: ${YELLOW}NECESITA MEJORAS${NC}"
            else
                echo -e "🎯 Nivel de seguridad: ${RED}CRÍTICO${NC}"
            fi
        fi
        
        echo ""
        echo -e "${CYAN}📄 Log detallado guardado en: $LOGFILE${NC}"
        
        # Mostrar últimos resultados
        echo -e "\n${YELLOW}📋 ÚLTIMOS RESULTADOS:${NC}"
        tail -10 "$LOGFILE" 2>/dev/null | sed 's/^/  /'
        
    else
        echo -e "${RED}No se encontró archivo de log${NC}"
    fi
}

# Loop principal
main_loop() {
    while true; do
        show_menu
        
        case $choice in
            1) test_basic ;;
            2) test_boolean_blind ;;
            3) test_union_based ;;
            4) test_time_based ;;
            5) test_error_based ;;
            6) test_auth_bypass ;;
            7) test_bypass_techniques ;;
            8) run_full_test ;;
            9) show_statistics ;;
            0) 
                echo -e "\n${GREEN}👋 ¡Gracias por usar Compareware Security Testing Suite!${NC}"
                echo -e "${CYAN}📄 Resultados guardados en: $LOGFILE${NC}"
                exit 0
                ;;
            *) 
                echo -e "${RED}❌ Opción inválida. Por favor selecciona 0-9.${NC}"
                ;;
        esac
        
        echo ""
        read -p "Presiona Enter para continuar..."
    done
}

# Función de ayuda
show_help() {
    echo -e "${CYAN}🆘 AYUDA - TESTING MANUAL SQL INJECTION${NC}"
    echo ""
    echo -e "${YELLOW}INTERPRETACIÓN DE RESULTADOS:${NC}"
    echo -e "  ${GREEN}✅ BLOCKED${NC}     - El WAF/validador bloqueó el ataque (SEGURO)"
    echo -e "  ${RED}🚨 VULNERABLE${NC}  - Se detectó una vulnerabilidad (CRÍTICO)" 
    echo -e "  ${YELLOW}⚠️  SUSPICIOUS${NC}  - Respuesta anómala que requiere investigación"
    echo -e "  ${CYAN}ℹ️  OTHER${NC}       - Respuesta no categorizada"
    echo ""
    echo -e "${YELLOW}QUÉ HACER SI ENCUENTRAS VULNERABILIDADES:${NC}"
    echo "  1. 🛑 NO entres en pánico"
    echo "  2. 📋 Documenta el hallazgo exacto"
    echo "  3. 🔍 Verifica que no sea un falso positivo"
    echo "  4. 🛠️  Implementa la corrección apropiada"
    echo "  5. 🧪 Re-testea para confirmar la corrección"
    echo ""
    echo -e "${YELLOW}CONSEJOS DE SEGURIDAD:${NC}"
    echo "  • Ejecuta estos tests SOLO en tu propio sistema"
    echo "  • Mantén logs de todos los tests"
    echo "  • Testea regularmente (al menos mensualmente)"
    echo "  • Revisa y actualiza las reglas de seguridad"
    echo ""
}

# Inicio del script
main() {
    print_banner
    
    echo -e "${BLUE}Inicializando sistema de testing...${NC}"
    echo -e "${CYAN}Log file: $LOGFILE${NC}"
    
    # Verificar servidor
    check_server
    
    # Mostrar ayuda inicial
    echo ""
    echo -e "${YELLOW}💡 Tip: Escribe 'help' en cualquier momento para ver ayuda${NC}"
    
    # Verificar si quiere ver ayuda
    echo ""
    read -p "¿Quieres ver la guía de ayuda antes de empezar? (y/n): " show_help_choice
    if [[ $show_help_choice =~ ^[Yy]$ ]]; then
        show_help
        echo ""
        read -p "Presiona Enter para continuar al menú principal..."
    fi
    
    # Loop principal
    main_loop
}

# Trap para cleanup
trap 'echo -e "\n${YELLOW}🧹 Limpiando y guardando resultados...${NC}"; exit 0' INT

# Ejecutar script
main "$@"