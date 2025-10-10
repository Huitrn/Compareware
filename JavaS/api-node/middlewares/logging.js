const { writeToLog } = require('../utils/logger');
const logConfig = require('../config/logConfig');

const logFailedAccess = (details) => {
  const { ip, userAgent, route, method, authType, username, reason, statusCode } = details;
  const logMessage = `FAILED_ACCESS | IP: ${ip} | METHOD: ${method} | ROUTE: ${route} | AUTH_TYPE: ${authType} | USERNAME: ${username || 'N/A'} | REASON: ${reason} | STATUS: ${statusCode} | USER_AGENT: ${userAgent}`;
  writeToLog(logConfig.failedAccessLog, logMessage);
  const securityMessage = `SECURITY_EVENT | TYPE: FAILED_AUTH | IP: ${ip} | ROUTE: ${route} | USER: ${username || 'UNKNOWN'} | REASON: ${reason}`;
  writeToLog(logConfig.securityLog, securityMessage);
  console.log(`🚨 INTENTO FALLIDO: ${ip} -> ${route} (${reason})`);
};

const logSecurityEvent = (type, details) => {
  const { ip, route, message, data } = details;
  const securityMessage = `SECURITY_EVENT | TYPE: ${type} | IP: ${ip || 'N/A'} | ROUTE: ${route || 'N/A'} | MESSAGE: ${message} | DATA: ${JSON.stringify(data || {})}`;
  writeToLog(logConfig.securityLog, securityMessage);
  console.log(`🔒 EVENTO DE SEGURIDAD: ${type} - ${message}`);
};

module.exports = { logFailedAccess, logSecurityEvent };
