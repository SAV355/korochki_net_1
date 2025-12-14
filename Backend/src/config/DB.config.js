// Здесь хронятся параметры подключения к базе данных

// Библиотека .env ()
require('dotenv').config();

module.exports = {
  host: process.env.DB_HOST || 'localhost',
  port: process.env.DB_PORT || 5432, // Порт по умолчанию
  database: process.env.DB_NAME || 'korochki_est',
  user: process.env.DB_USER || 'postgres', // Имя пользователя 
  password: process.env.DB_PASSWORD || 'password', 
};
