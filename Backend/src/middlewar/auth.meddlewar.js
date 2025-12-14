// Здесь производится проверка авторизации пользователя (JWT-токена)

const jwt = require('jsonwebtoken');

module.exports = function (req, res, next) {
    // Проверка браузеры отправляют OPTIONS-запрос (preflight)
    if (req.method === "OPTIONS") {
        return next();
    }

    try {
        // 1. Получаем токен из заголовка Authorization.
        const token = req.headers.authorization.split(' ')[1];

        if (!token) {
            return res.status(401).json({ message: "Пользователь не авторизован" });
        }

        // 2. Расшифровываем токен с помощью ключа.
        const decodedData = jwt.verify(token, process.env.JWT_SECRET);

        // 3. Добавляем данные из токена (id, role) в объект запроса.

        req.user = decodedData;

        // 4. Передаем управление следующему middleware в цепочке или конечному обработчику маршрута.
        next();

    } catch (e) {
        // Проверка если токена нет, или он невалидный, jwt.verify выбросит ошибку,
        console.error("Auth middleware error:", e.message);
        return res.status(401).json({ message: "Пользователь не авторизован" });
    }
};
