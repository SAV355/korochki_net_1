//Здесь будет реализована логика управление пользователями

const db = require('../db');

/**
 * @description Получение данных о текущем пользователе (по токену)
 * @route GET /api/users/me
 */
class UserController {
  async getCurrentUser(req, res) {
    try {
      // ID пользователя добавляется в объект req в middleware аутентификации
      const userId = req.user.id; 

      const userResult = await db.query('SELECT id, login, full_name, phone, email, role FROM users WHERE id = $1', [userId]);

      if (userResult.rowCount === 0) {
        return res.status(404).json({ message: 'Пользователь не найден' });
      }

      res.json(userResult.rows[0]);
    } catch (e) {
      console.error(e);
      res.status(500).json({ message: 'Ошибка на сервере' });
    }
  }
}

module.exports = new UserController();
