<?php
/*  Главная страница
*
*   Через неё происходит взаимодействие пользователя (потенциального работодателя)
*   с примерами задач, которые задал он (или другие работодатели до него).
*
*   Автор проекта - Игорь Ондар.
*                   io@azbukatuva.ru
*/

namespace snaiperk\interview;

/* 
 * Решаем вопрос автозагрузки
 * Этот include-файл не мой - он скачан с сайта PSR.
 */
include 'core/psr/Psr4AutoloaderClass.php';             /* Несмотря на то, что расположение этого файла немного нарушает
                                                         * общую систему именования, это не страшно, потому что он такой один.
                                                         * ...и потому что без него всё не будет работать как надо! */
$loader = new \Psr4\Autoloader\Psr4AutoloaderClass;     // Создадим экземпляр загрузчика
$loader->register();                                    // Зарегистрируем метод
$loader->addNamespace('snaiperk\interview', $_SERVER['DOCUMENT_ROOT']);          // Мудрить не будем, исходники лежат недалеко
$loader->addNamespace('snaiperk\interview', $_SERVER['DOCUMENT_ROOT'].'/tests'); // Юнит-тесты - тоже.
$loader->addNamespace('Psr', $_SERVER['DOCUMENT_ROOT'].'/core/psr');             // Методы и классы PSR

/*
 *  Класс phpTestDirector - синглтон, поэтому его конструктор немного заныкан.
 */
$director = \snaiperk\interview\core\phpTestDirector::getInstance();        // Создаём объект, управляющий логикой приложения
                                                                            // В момент создания он сам определяет, что делать сейчас
$director->setBaseDir([         // Базовые директории нужны для тех случаев, когда приходится искать в открытом множестве сущностей
    'templates' => $_SERVER['DOCUMENT_ROOT'].'/templates',                  // Задаём директорию, в которой лежат шаблоны вывода
    'testcases' => $_SERVER['DOCUMENT_ROOT'].'/testcases'                   // И директорию, в которой лежат классы тестовых заданий
]); 

$director->addContextFields([   // Контекстные поля нужны для тех случаев, когда в шаблоне чётко указано название поля
    'ТАБЛИЦА СТИЛЕЙ' => 'stylesheets/interview-testcases.css',              // Укажем таблицу стилей для шаблона
    'СКРИПТ JS'      => 'javascripts/interview-testcases.js'                // И для скрипта
]);

// Наконец, отдадим клиенту готовую страницу!
echo $director->render();