// Главный файл для запуска сервера


// Загружаем переменные окружения из .env
require('dotenv').config();

const express = require('express');
const cors = require('cors');

// Импортирт маршрутизаторов
const authRouter = require('./src/routes/auth.routes');
const userRouter = require('./src/routes/user.routes');
const applicationRouter = require('./src/routes/application.routes');

const PORT = process.env.PORT || 5000; 

const app = express();

// Настраиваем middleware
app.use(cors()); // Позволяет принимать запросы с других доменов 
app.use(express.json()); // Позволяет парсить входящие JSON-запросы

// Подключаем маршруты
// Все маршруты, связанные с аутентификацией, 
app.use('/api/auth', authRouter); //
// Маршруты для работы с пользователями
app.use('/api/users', userRouter);
// Маршруты для работы с заявками
app.use('/api/applications', applicationRouter); 

const start = async () => {
    try {
    // можно добавить проверку соединения 
    app.listen(PORT, () => console.log(`Сервер запущен на порту ${PORT}...`));
    } catch (e) {
        console.error('Ошибка при запуске сервера:', e);
    }
};

start();
