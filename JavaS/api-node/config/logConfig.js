const path = require('path');

module.exports = {
  logDir: path.join(__dirname, '../logs'),
  failedAccessLog: path.join(__dirname, '../logs', 'failed-access.log'),
  securityLog: path.join(__dirname, '../logs', 'security.log'),
  maxLogSize: 10 * 1024 * 1024,
};
