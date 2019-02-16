INSERT INTO projects (name, user_id)
VALUES
  ('Входящие', 1),
  ('Учёба', 1),
  ('Работа', 2),
  ('Домашние дела', 2),
  ('Авто', 1);

INSERT INTO tasks (name, date_deadline, status, user_id, project_id)
VALUES
  ('Собеседование в IT компании', '13.03.2019', 0, 2, 3),
  ('Выполнить тестовое задание', '20.03.2019', 0, 2, 3),
  ('Сделать задание первого раздела', '01.03.2019', 1, 1, 2),
  ('Встреча с другом', '11.03.2019', 0, 1, 1),
  ('Купить корм для кота', NUll, 0, 2, 4),
  ('Заказать пиццу', NUll, 0, 2, 4);

INSERT INTO users (name, email, password)
VALUES
  ('Константин Константинопольский', 'konstantin@mail.ru', 'nitnatsnok'),
  ('Монти Пайтон', 'pie@mail.ru', 'mountainpie');

-- Получить список из всех проектов для пользователя с id 1
SELECT * FROM projects
WHERE user_id = 1;

-- Получить список из всех задач для проекта с id 4
SELECT * FROM tasks
WHERE project_id = 4;

-- Пометить задачу как выполненную у задачи с id 1
UPDATE tasks SET status = 1
WHERE id = 1;

-- Обновить название задачи с id 6 на 'Заказать бургеры'
UPDATE tasks SET name = 'Заказать бургеры'
WHERE id = 6;
