const BaseRepository = require('./BaseRepository');
const bcrypt = require('bcryptjs');

class UserRepository extends BaseRepository {
  constructor() {
    super('users');
  }

  /**
   * Crear usuario con hash de contraseña
   */
  async createUser(userData, client = null) {
    try {
      // Hash de la contraseña
      const hashedPassword = await bcrypt.hash(userData.password, 12);
      
      const userToCreate = {
        ...userData,
        password: hashedPassword,
        created_at: new Date(),
        updated_at: new Date()
      };

      const user = await this.create(userToCreate, client);
      
      // No retornar la contraseña
      const { password, ...userWithoutPassword } = user;
      
      this.logOperation('USER_CREATED', {
        userId: user.id,
        email: user.email,
        name: user.name
      });

      return userWithoutPassword;
    } catch (error) {
      this.logOperation('USER_CREATION_ERROR', {
        email: userData.email,
        error: error.message
      });
      throw error;
    }
  }

  /**
   * Buscar usuario por email
   */
  async findByEmail(email, client = null) {
    const query = 'SELECT * FROM users WHERE email = $1';
    const result = await this.executeQuery(query, [email], client);
    
    this.logOperation('USER_SEARCH_BY_EMAIL', {
      email: email,
      found: result.rows.length > 0
    });

    return result.rows[0] || null;
  }

  /**
   * Verificar contraseña
   */
  async verifyPassword(plainPassword, hashedPassword) {
    try {
      const isValid = await bcrypt.compare(plainPassword, hashedPassword);
      
      this.logOperation('PASSWORD_VERIFICATION', {
        result: isValid ? 'VALID' : 'INVALID'
      });

      return isValid;
    } catch (error) {
      this.logOperation('PASSWORD_VERIFICATION_ERROR', {
        error: error.message
      });
      return false;
    }
  }

  /**
   * Actualizar último login
   */
  async updateLastLogin(userId, client = null) {
    const query = `
      UPDATE users 
      SET last_login_at = NOW(), updated_at = NOW()
      WHERE id = $1 
      RETURNING last_login_at
    `;
    
    const result = await this.executeQuery(query, [userId], client);
    
    this.logOperation('USER_LAST_LOGIN_UPDATED', {
      userId: userId,
      loginTime: result.rows[0]?.last_login_at
    });

    return result.rows[0];
  }

  /**
   * Buscar usuarios activos
   */
  async findActiveUsers(client = null) {
    const query = `
      SELECT id, name, email, created_at, last_login_at
      FROM users 
      WHERE deleted_at IS NULL 
      ORDER BY created_at DESC
    `;
    
    const result = await this.executeQuery(query, [], client);
    
    this.logOperation('ACTIVE_USERS_FETCHED', {
      count: result.rows.length
    });

    return result.rows;
  }

  /**
   * Soft delete de usuario
   */
  async softDelete(userId, client = null) {
    const query = `
      UPDATE users 
      SET deleted_at = NOW(), updated_at = NOW()
      WHERE id = $1 AND deleted_at IS NULL
      RETURNING id, email, name
    `;
    
    const result = await this.executeQuery(query, [userId], client);
    
    if (result.rows.length > 0) {
      this.logOperation('USER_SOFT_DELETED', {
        userId: userId,
        email: result.rows[0].email
      });
    }

    return result.rows[0] || null;
  }

  /**
   * Restaurar usuario eliminado
   */
  async restore(userId, client = null) {
    const query = `
      UPDATE users 
      SET deleted_at = NULL, updated_at = NOW()
      WHERE id = $1 AND deleted_at IS NOT NULL
      RETURNING id, email, name
    `;
    
    const result = await this.executeQuery(query, [userId], client);
    
    if (result.rows.length > 0) {
      this.logOperation('USER_RESTORED', {
        userId: userId,
        email: result.rows[0].email
      });
    }

    return result.rows[0] || null;
  }

  /**
   * Buscar usuario por ID sin contraseña
   */
  async findByIdSecure(id, client = null) {
    const query = `
      SELECT id, name, email, created_at, updated_at, last_login_at
      FROM users 
      WHERE id = $1 AND deleted_at IS NULL
    `;
    
    const result = await this.executeQuery(query, [id], client);
    
    this.logOperation('USER_SECURE_FETCH', {
      userId: id,
      found: result.rows.length > 0
    });

    return result.rows[0] || null;
  }

  /**
   * Cambiar contraseña
   */
  async changePassword(userId, newPassword, client = null) {
    try {
      const hashedPassword = await bcrypt.hash(newPassword, 12);
      
      const query = `
        UPDATE users 
        SET password = $1, updated_at = NOW()
        WHERE id = $2 AND deleted_at IS NULL
        RETURNING id, email
      `;
      
      const result = await this.executeQuery(query, [hashedPassword, userId], client);
      
      if (result.rows.length > 0) {
        this.logOperation('USER_PASSWORD_CHANGED', {
          userId: userId,
          email: result.rows[0].email
        });
      }

      return result.rows[0] || null;
    } catch (error) {
      this.logOperation('USER_PASSWORD_CHANGE_ERROR', {
        userId: userId,
        error: error.message
      });
      throw error;
    }
  }
}

module.exports = UserRepository;