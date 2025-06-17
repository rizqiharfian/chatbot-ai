CREATE DATABASE chatbot_db;
USE chatbot_db;

CREATE TABLE messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sender ENUM('user', 'bot') NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);