const fs = require('fs');
const logConfig = require('../config/logConfig');

const writeToLog = (logFile, message) => {
  try {
    const timestamp = new Date().toISOString();
    const logEntry = `[${timestamp}] ${message}\n`;
    fs.appendFile(logFile, logEntry, (err) => {
      if (err) console.error('❌ Error escribiendo log:', err);
    });
  } catch (error) {
    console.error('❌ Error en writeToLog:', error);
  }
};

module.exports = { writeToLog };
