CREATE DATABASE doingsdone
DEFAULT CHARACTER SET utf8
DEFAULT COLLATE utf8_general_ci;

USE doingsdone;

CREATE TABLE projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(128) NOT NUll,
  user_id INT NOT NULL
);

CREATE INDEX project_name ON projects(name);

CREATE TABLE tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(128) NOT NULL,
  date_add TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  date_deadline TIMESTAMP NULL,
  status BOOLEAN DEFAULT 0,
  date_complete TIMESTAMP NULL,
  file VARCHAR(128),
  user_id INT NOT NULL,
  project_id INT
);

CREATE INDEX task_name ON tasks(name);

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  date_register TIMESTAMP,
  name VARCHAR(128) NOT NULL,
  email VARCHAR(64) NOT NULL UNIQUE,
  password VARCHAR(64) NOT NULL
);

CREATE INDEX user_name ON users(name);
CREATE UNIQUE INDEX user_email ON users(email);
