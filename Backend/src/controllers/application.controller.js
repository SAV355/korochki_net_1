//Здесь будет реализованна логика управление заявками и их статусами

const db = require('../db');

class ApplicationController {
  /**
   * @description Создание новой заявки
   * @route POST /api/applications
   */
  async createApplication(req, res) {
    try {
      const { courseId, desiredStartDate, paymentMethod } = req.body;
      const userId = req.user.id; // ID берем из токена

      if (!courseId || !desiredStartDate || !paymentMethod) {
        return res.status(400).json({ message: 'Не все поля заявки заполнены' });
      }

      // Статус "Новая" присваивается по умолчанию
      const newApplication = await db.query(
        'INSERT INTO applications (user_id, course_id, desired_start_date, payment_method) VALUES (\$1, \$2, \$3, \$4) RETURNING *',
        [userId, courseId, desiredStartDate, paymentMethod]
      );

      res.status(201).json(newApplication.rows[0]);
    } catch (e) {
      console.error(e);
      res.status(500).json({ message: 'Ошибка при создании заявки' });
    }
  }

  /**
   * @description //Получение заявок ТЕКУЩЕГО пользователя
   * @route GET /api/applications/my
   */
  async getMyApplications(req, res) {
    try {
      const userId = req.user.id;
      const applications = await db.query(
        `SELECT a.id, a.status, a.desired_start_date, a.created_at, c.name as course_name 
         FROM applications a
         JOIN courses c ON a.course_id = c.id
         WHERE a.user_id = \$1 ORDER BY a.created_at DESC`,
        [userId]
      );

      res.json(applications.rows);
    } catch (e) {
      console.error(e);
      res.status(500).json({ message: 'Ошибка при получении заявок' });
    }
  }

  /**
   * @description //Получение заявок (для администратора)
   * @route GET /api/applications
   */
  async getAllApplications(req, res) {
    try {
      // Фильтр по статус, для администратора
      const { status } = req.query;
      let queryText = `
        SELECT a.id, a.status, a.desired_start_date, a.created_at, c.name as course_name, u.full_name as user_name
        FROM applications a
        JOIN courses c ON a.course_id = c.id
        JOIN users u ON a.user_id = u.id`;
      
      const params = [];
      if (status) {
        queryText += ' WHERE a.status = \$1';
        params.push(status);
      }
      queryText += ' ORDER BY a.created_at DESC';

      const applications = await db.query(queryText, params);

      res.json(applications.rows);
    } catch (e) {
      console.error(e);
      res.status(500).json({ message: 'Ошибка при получении всех заявок' });
    }
  }

  /**
   * @description //Изменение статуса заявки (для администратора)
   * @route PATCH /api/applications/:id
   */
  async updateApplicationStatus(req, res) {
    try {
      const { id } = req.params;
      const { status } = req.body;

      // Проверка, статуса
      const allowedStatuses = ['Идет обучение', 'Обучение завершено'];
      if (!allowedStatuses.includes(status)) {
        return res.status(400).json({ message: 'Недопустимый статус заявки' });
      }

      const updatedApplication = await db.query(
        'UPDATE applications SET status = \$1 WHERE id = \$2 RETURNING *',
        [status, id]
      );
      
      if (updatedApplication.rowCount === 0) {
        return res.status(404).json({ message: 'Заявка не найдена' });
      }

      res.json(updatedApplication.rows[0]);
    } catch (e) {
      console.error(e);
      res.status(500).json({ message: 'Ошибка при обновлении статуса' });
    }
  }

  /**
   * @description //Вывод списка доступных курсов
   * @route GET /api/courses
   */
  async getCourses(req, res) {
    try {
      const courses = await db.query('SELECT * FROM courses ORDER BY name ASC');
      res.json(courses.rows);
    } catch (e) {
      console.error(e);
      res.status(500).json({ message: 'Ошибка при получении списка курсов' });
    }
  }
}

module.exports = new ApplicationController();
