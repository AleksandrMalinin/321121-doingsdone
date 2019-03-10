<?php
require_once('./vendor/autoload.php');
require_once('./functions.php');
require_once('./init.php');

// указываем данные для доступа к SMTP серверу
$transport = new Swift_SmtpTransport('phpdemo.ru', 25);
$transport->setUsername('keks@phpdemo.ru');
$transport->setPassword('htmlacademy');

// создаём объект, ответственный за отправку сообщений и передаём туда объект с SMTP сервером
$mailer = new Swift_Mailer($transport);

// журналируем данные
$logger = new Swift_Plugins_Loggers_ArrayLogger();
$mailer->registerPlugin(new Swift_Plugins_LoggerPlugin($logger));

// запрашиваем данные
$tasks = get_data_for_notify($connect, []);

// если есть задачи на сегодняшний день
if ($tasks) {
    foreach ($tasks as $task) {
        // склоняем в зависимости от количества задач
        $notification_message = strpos($task['list'], ',') ? 'У вас запланированы задачи ' : 'У вас запланирована задача ';
        $notification_message .= $task['list'];

        $message = new Swift_Message();
        $message->setSubject('Уведомление от сервиса «Дела в порядке»');
        $message->setFrom(['keks@phpdemo.ru' => 'doignsdone']);
        $message->setTo([$task['email'] => $task['user_name']]);
        $message->setBody(
            'Уважаемый ' . $task['user_name'] . '!<br>' . $notification_message . ' на сегодняшний день.',
            'text/html'
        );

        $result = $mailer->send($message);
    }

    if ($result) {
        print("Рассылка успешно отправлена");
    } else {
        print("Не удалось отправить рассылку: " . $logger->dump());
    }
}
