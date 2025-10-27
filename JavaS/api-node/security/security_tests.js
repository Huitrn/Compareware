/**
 * Script de Ejemplo para Ejecutar Penetration Tests
 * Proyecto: Compareware - Desarrollo Backend
 * 
 * ⚠️  IMPORTANTE: Solo usar en tu propio sistema de desarrollo/testing
 */

const { SQLInjectionPenTester } = require('./SQLInjectionPenTester');

async function runSecurityTests() {
  console.log('🛡️  COMPAREWARE SECURITY TESTING SUITE');
  console.log('=====================================\n');

  // Configurar para tu servidor local
  const BASE_URL = 'http://localhost:3000'; // Cambiar por tu URL
  
  const penTester = new SQLInjectionPenTester(BASE_URL);

  try {
    // Ejecutar suite completa
    await penTester.runFullPenTest();

    console.log('\n✅ Penetration test completado exitosamente!');
    console.log('📄 Revisa el reporte generado en la carpeta logs/');

  } catch (error) {
    console.error('❌ Error ejecutando tests:', error.message);
  }
}

// Función para testing específico
async function testSpecificPayload() {
  const BASE_URL = 'http://localhost:3000';
  const penTester = new SQLInjectionPenTester(BASE_URL);

  // Test específico
  const payload = "' OR '1'='1'--";
  const endpoint = '/api/users';
  
  console.log(`🎯 Testing específico: ${endpoint} con ${payload}`);
  
  try {
    const result = await penTester.quickTest(endpoint, payload);
    console.log('Resultado:', JSON.stringify(result, null, 2));
  } catch (error) {
    console.error('Error:', error.message);
  }
}

// Ejecutar si se llama directamente
if (require.main === module) {
  console.log('Selecciona el tipo de test:');
  console.log('1. Suite completa de penetration testing');
  console.log('2. Test específico de un payload');
  
  const args = process.argv.slice(2);
  
  if (args[0] === 'full' || !args[0]) {
    runSecurityTests();
  } else if (args[0] === 'specific') {
    testSpecificPayload();
  } else {
    console.log('Uso: node security_tests.js [full|specific]');
  }
}

module.exports = {
  runSecurityTests,
  testSpecificPayload
};