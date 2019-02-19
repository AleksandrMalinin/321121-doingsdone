INSERT INTO projects (name, user_id)
VALUES
  ('Входящие', 1),
  ('Учёба', 1),
  ('Работа', 2),
  ('Домашние дела', 2),
  ('Авто', 1),
  ('Cпорт', 3),
  ('Досуг', 3);

INSERT INTO tasks (name, date_deadline, status, user_id, project_id)
VALUES
  ('Собеседование в IT компании', '13.03.2019', 0, 2, 3),
  ('Выполнить тестовое задание', '20.03.2019', 0, 2, 3),
  ('Сделать задание первого раздела', '01.03.2019', 1, 1, 2),
  ('Встреча с другом', '11.03.2019', 0, 1, 1),
  ('Купить корм для кота', NUll, 0, 2, 4),
  ('Заказать пиццу', NUll, 0, 2, 4),
  ('Сходить в бассейн', '10z.03.2019', 0, 3, 6),
  ('Пробежка', '18.02.2019', 0, 3, 6),
  ('Сходить с мужиками в баню', '15.03.2019', 0, 3, 7),
  ('Выбраться за город', '20.02.2019', 0, 3, 7);

INSERT INTO users (name, email, password)
VALUES
  ('Константин Константинопольский', 'konstantin@mail.ru', 'nitnatsnok'),
  ('Монти Пайтон', 'pie@mail.ru', 'mountainpie'),
  ('Семён Павлович Ковчег', 'spk@mail.ru', 'tvar');

-- Получить список из всех проектов для пользователя с id 1
SELECT * FROM projects
WHERE user_id = 1;

-- Получить список из всех задач для проекта с id 4
SELECT * FROM tasks
WHERE project_id = 4;

-- Пометить задачу с id 1 как выполненную
UPDATE tasks SET status = 1
WHERE id = 1;

-- Обновить название задачи с id 6 на 'Заказать бургеры'
UPDATE tasks SET name = 'Заказать бургеры'
WHERE id = 6;

-- Пометить задачу с id 8 как выполненную
UPDATE tasks SET status = 1
WHERE id = 8;
