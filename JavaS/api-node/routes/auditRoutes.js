const express = require('express');
const AuditLogRepository = require('../repositories/AuditLogRepository');
const { authenticateToken } = require('../middlewares/auth');
const { logSecurityEvent } = require('../middlewares/logging');

const router = express.Router();
const auditLogRepository = new AuditLogRepository();

/**
 * @route GET /api/audit/transaction/:transactionId
 * @desc Obtener logs de auditoría por transacción
 * @access Private (Admin)
 */
router.get('/transaction/:transactionId', 
  authenticateToken,
  async (req, res) => {
    try {
      const { transactionId } = req.params;
      
      const logs = await auditLogRepository.findByTransactionId(transactionId);
      
      logSecurityEvent('AUDIT_LOGS_QUERIED', {
        ip: req.ip,
        route: req.originalUrl,
        message: 'Logs de auditoría consultados por transacción',
        data: {
          transactionId: transactionId,
          userId: req.user?.id,
          logsCount: logs.length
        }
      });

      res.status(200).json({
        success: true,
        message: 'Logs de auditoría obtenidos exitosamente',
        data: {
          transactionId: transactionId,
          logs: logs,
          count: logs.length
        }
      });

    } catch (error) {
      console.error('Error obteniendo logs de auditoría:', error.message);
      res.status(500).json({
        success: false,
        message: 'Error interno del servidor',
        error: process.env.NODE_ENV === 'development' ? error.message : 'INTERNAL_SERVER_ERROR'
      });
    }
  }
);

/**
 * @route GET /api/audit/user/:userId
 * @desc Obtener logs de auditoría por usuario
 * @access Private (Admin)
 */
router.get('/user/:userId', 
  authenticateToken,
  async (req, res) => {
    try {
      const { userId } = req.params;
      const page = parseInt(req.query.page) || 1;
      const limit = parseInt(req.query.limit) || 20;
      
      const logs = await auditLogRepository.findByUserId(userId, page, limit);
      
      res.status(200).json({
        success: true,
        message: 'Logs de usuario obtenidos exitosamente',
        data: {
          userId: userId,
          logs: logs,
          pagination: {
            page: page,
            limit: limit,
            total: logs.length
          }
        }
      });

    } catch (error) {
      console.error('Error obteniendo logs de usuario:', error.message);
      res.status(500).json({
        success: false,
        message: 'Error interno del servidor',
        error: process.env.NODE_ENV === 'development' ? error.message : 'INTERNAL_SERVER_ERROR'
      });
    }
  }
);

/**
 * @route GET /api/audit/entity/:entityType/:entityId
 * @desc Obtener logs de auditoría por entidad
 * @access Private (Admin)
 */
router.get('/entity/:entityType/:entityId', 
  authenticateToken,
  async (req, res) => {
    try {
      const { entityType, entityId } = req.params;
      
      const logs = await auditLogRepository.findByEntity(entityType.toUpperCase(), entityId);
      
      res.status(200).json({
        success: true,
        message: 'Logs de entidad obtenidos exitosamente',
        data: {
          entityType: entityType.toUpperCase(),
          entityId: entityId,
          logs: logs,
          count: logs.length
        }
      });

    } catch (error) {
      console.error('Error obteniendo logs de entidad:', error.message);
      res.status(500).json({
        success: false,
        message: 'Error interno del servidor',
        error: process.env.NODE_ENV === 'development' ? error.message : 'INTERNAL_SERVER_ERROR'
      });
    }
  }
);

/**
 * @route GET /api/audit/action/:action
 * @desc Obtener logs de auditoría por acción
 * @access Private (Admin)
 */
router.get('/action/:action', 
  authenticateToken,
  async (req, res) => {
    try {
      const { action } = req.params;
      const { date_from, date_to, page = 1, limit = 50 } = req.query;
      
      const dateFrom = date_from ? new Date(date_from) : null;
      const dateTo = date_to ? new Date(date_to) : null;
      
      const logs = await auditLogRepository.findByAction(
        action.toUpperCase(), 
        dateFrom, 
        dateTo, 
        parseInt(page), 
        parseInt(limit)
      );
      
      res.status(200).json({
        success: true,
        message: 'Logs de acción obtenidos exitosamente',
        data: {
          action: action.toUpperCase(),
          filters: {
            dateFrom: dateFrom,
            dateTo: dateTo
          },
          logs: logs,
          pagination: {
            page: parseInt(page),
            limit: parseInt(limit),
            total: logs.length
          }
        }
      });

    } catch (error) {
      console.error('Error obteniendo logs de acción:', error.message);
      res.status(500).json({
        success: false,
        message: 'Error interno del servidor',
        error: process.env.NODE_ENV === 'development' ? error.message : 'INTERNAL_SERVER_ERROR'
      });
    }
  }
);

/**
 * @route GET /api/audit/stats
 * @desc Obtener estadísticas de auditoría
 * @access Private (Admin)
 */
router.get('/stats', 
  authenticateToken,
  async (req, res) => {
    try {
      const days = parseInt(req.query.days) || 7;
      
      const stats = await auditLogRepository.getAuditStats(days);
      const failedTransactions = await auditLogRepository.findFailedTransactions(24);
      
      res.status(200).json({
        success: true,
        message: 'Estadísticas de auditoría obtenidas exitosamente',
        data: {
          period_days: days,
          statistics: stats,
          failed_transactions_last_24h: failedTransactions.length,
          failed_transactions_details: failedTransactions
        }
      });

    } catch (error) {
      console.error('Error obteniendo estadísticas de auditoría:', error.message);
      res.status(500).json({
        success: false,
        message: 'Error interno del servidor',
        error: process.env.NODE_ENV === 'development' ? error.message : 'INTERNAL_SERVER_ERROR'
      });
    }
  }
);

/**
 * @route DELETE /api/audit/cleanup
 * @desc Limpiar logs antiguos de auditoría
 * @access Private (Admin)
 */
router.delete('/cleanup', 
  authenticateToken,
  async (req, res) => {
    try {
      const daysToKeep = parseInt(req.query.days) || 90;
      
      const result = await auditLogRepository.cleanOldLogs(daysToKeep);
      
      logSecurityEvent('AUDIT_LOGS_CLEANED', {
        ip: req.ip,
        route: req.originalUrl,
        message: 'Limpieza de logs de auditoría ejecutada',
        data: {
          userId: req.user?.id,
          daysToKeep: daysToKeep,
          deletedCount: result.deleted
        }
      });
      
      res.status(200).json({
        success: true,
        message: 'Limpieza de logs completada exitosamente',
        data: result
      });

    } catch (error) {
      console.error('Error limpiando logs:', error.message);
      res.status(500).json({
        success: false,
        message: 'Error interno del servidor',
        error: process.env.NODE_ENV === 'development' ? error.message : 'INTERNAL_SERVER_ERROR'
      });
    }
  }
);

module.exports = router;