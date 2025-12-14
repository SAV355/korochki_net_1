

const db = require('../db'); // импортирует MySQL-пул
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');

class AuthController {
  async register(req, res) {
    try {
      const { login, password, fullName, phone, email } = req.body;

      if (!login || !password || !fullName || !phone || !email) {
        return res.status(400).json({ message: 'Все поля обязательны для заполнения' });
      }


      const [candidates] = await db.query('SELECT * FROM users WHERE login = ? OR email = ?', [login, email]);

      // Проверка длины массива
      if (candidates.length > 0) {
        return res.status(409).json({ message: 'Пользователь с таким логином или email уже существует' }); // [T5](5)
      }

      const salt = bcrypt.genSaltSync(10);
      const passwordHash = bcrypt.hashSync(password, salt);


      const [newUserResult] = await db.query(
        'INSERT INTO users (login, password_hash, full_name, phone, email) VALUES (?, ?, ?, ?, ?)',
        [login, passwordHash, fullName, phone, email]
      );

      // Получаем ID пользователя
      const newUserId = newUserResult.insertId;

      return res.status(201).json({ message: 'Пользователь успешно зарегистрирован', user: { id: newUserId, login } });
    } catch (e) {
      console.error(e);
      res.status(500).json({ message: 'Ошибка на сервере при регистрации' });
    }
  }

  async login(req, res) {
    try {
      const { login, password } = req.body;
      

      const [users] = await db.query('SELECT * FROM users WHERE login = ?', [login]);
      const user = users[0];

      if (!user) {
        return res.status(400).json({ message: 'Неверный логин или пароль' }); // [T2](3)
      }
      
      // 2. Проверка пароля 
      const isPasswordValid = bcrypt.compareSync(password, user.password_hash);
      if (!isPasswordValid) {
        return res.status(400).json({ message: 'Неверный логин или пароль' }); // [T2](3)
      }

      const token = jwt.sign(
        { id: user.id, role: user.role },
        process.env.JWT_SECRET,
        { expiresIn: '24h' }
      );

      return res.json({
        token,
        user: {
          id: user.id,
          login: user.login,
          role: user.role,
        },
      });
    } catch (e) {
      console.error(e);
      res.status(500).json({ message: 'Ошибка на сервере при авторизации' });
    }
  }
}

module.exports = new AuthController();
