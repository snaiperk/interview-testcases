<?php

namespace snaiperk\interview\core;

/*  phpTestDirector
 *  Синглтон, отвечающий за отображение конкретного текущего теста
 TODO:
 Так как этот класс у меня был уже практически написан, но не закоммичен, я его потерял.
 Придётся писать всё заново :(
    
 Методы:
    Работа с шаблонами
    ну и так далее...
 */
class phpTestDirector
{
    private static $testDirector    = null; // Объект-одиночка
    
    private $phpTestCases           = [];   // Массив найденных решений
    private $baseDir                = [];   // Массив базовых директорий
    private $contextFields          = [];   // Массив полей подстановки
    private $mergeFields            = [];   // Массив всех полей, найденных в шаблоне
    private $testForm               = [];   // Массив полей для ввода данных в тест
    
    private $workingMode            = '';   // Текущее "рабочее состояние" приложения (мы ищем и выводим тесты, показываем один тест или считаем его)
    private $workingModeDefault     = 'select-testcase'; // Режим работы "по умолчанию"
    private $currentTemplate        = '';   // Текущий шаблон отображения, текст
    
    private $curTest;
/******************************************************************************
    getInstance()
    Возвращает ссылку на экземпляр класса-синглтона phpTestDirector
    
    Параметры:
        нет
        
    Возвращаемое значение:
        Объект phpTestDirector
*/
    public static function getInstance()
    {
        if (!phpTestDirector::$testDirector) {
            phpTestDirector::$testDirector = new phpTestDirector();
        }
        return phpTestDirector::$testDirector;
    }
/******************************************************************************
    __construct()
    Выполняет инициализацию объекта: определяет из запроса текущую стадию работы,
    опрашивает объекты тестов на предмет результата и т.п.
    
    Параметры:
        нет
        
    Возвращаемое значение:
        Конструктор, значений не возвращает
*/
    private function __construct()
    {
        // 1. Определим текущий режим работы
        $this->workingMode = $this->defaultValue('phpTestDirector_workingMode', $this->workingModeDefault);
        
        // 2. Определим для себя алгоритм работы в каждом режиме
        switch ($this->workingMode) {
            case 'select-testcase':
                
                break;
            case 'view-test':
                
                break;
            case 'view-test-result':
                
                break;
            default:
                // TODO: Надо сделать шаблон для неправильного режима
        }
    }
/******************************************************************************
    defaultValue($argName, $defaultVal = 0)
    Ищет в HTTP-запросе переменную с именем, переданным в $argName. Если находит - 
    возвращае её значение. В противном случае возвращает $defaultValue.
    
    Параметры:
        $argName - строка, имя переменной
        $defaultValue - (mixed), значение по умолчанию
        
    Возвращаемое значение:
        (mixed) Значение, найденное в запросе, либо defaultValue
*/        
    private function defaultValue($argName, $defaultVal = 0)
    {
        return (isset($_REQUEST[$argName]) ? $_REQUEST[$argName] : $defaultVal);
    }		
/******************************************************************************
    processTestForm()
    Формирует из массива полей объекта phpTestCase HTML-код для вставки в шаблон
    Форма для ввода исходных данных на стадии запуска теста
    
    Параметры:
        Нет
        
    Возвращаемое значение:
        Строка, HTML-код формы для вставки в шаблон
*/     
    private function ProcessTestForm()
    {
        $template = "<h2>Называется \"".$this->testForm['name']."\"</h2> <br> <form method='post'>\n";
        foreach ($this->testForm['fields'] as $field => $properties) {
            $inputPrefix = "{$properties['caption']} <input type='{$properties['type']}' name='$field' autocomplete='off' value='";
            if (isset($properties['value'])) {
                if (is_array($properties['value'])) {
                    foreach ($properties['value'] as $num => $value) {
                        $template .= "$inputPrefix$value' />\n";
                    }
                } else {
                    $template .= "$inputPrefix$value' />\n";
                }
            } else {
                $template .= "$inputPrefix{$this->defaultValue($field, $properties['useDefault'])}' />\n";
            }
            if (isset($properties['newline'])) {
                $template .= str_repeat('<br />', $properties['newline']);
            }
        }
        $template .= "</form>";
        return $template;
    }

/******************************************************************************
    setBaseDir($key, $dir='')
    Задаёт начальную директорию по ключу $key, с конечным слешем /
    
    Параметры:
        $key - строка (ключ)
               массив (ключ-директория)
        $dir - строка (путь директории)
               не используется, по умолчанию пустая строка
        
    Возвращаемое значение:
        Integer, количество добавленных записей
*/
    public function setBaseDir($key, $dir='')
    {
        $affected = 0;
        if (is_array($key)) {
            $dirArray = $key;
        } elseif (is_string($key)) {
            $dirArray = [$key => $dir];
        } else {
            $dirArray = [];
        }
        
        foreach ($dirArray as $key => $dir) {
            if (is_dir($dir)) {
                $this->baseDir[$key] = $dir . (substr($dir, -1) == '/' ? '' : '/');
                $affected++;
            }
        }
        
        return $affected;
    }    

/******************************************************************************
    getBaseDir($key)
    Возвращает начальную директорию по ключу $key
    
    Параметры:
        $key - строка (ключ)
        
    Возвращаемое значение:
        Строка, путь к директории или пустая строка, если не найдено
*/
    public function getBaseDir($key)
    {        
        return (isset($this->baseDir[$key]) ? $this->baseDir[$key] : '');
    }
    
/******************************************************************************
    addContextFields($key, $value='')
    Добавляет или обновляет значения в массиве контекстных полей
    Эти значения используются для последующей подстановки в шаблоны HTML-страниц
    
    Параметры:
        $key    - строка (ключ)
                  массив (ключ-значение)
        $value  - строка (значение)
                  не используется, по умолчанию пустая строка
        
    Возвращаемое значение:
        Нет (т.к. все записи добавятся практически в любом случае)
*/
    public function addContextFields($key, $value='')
    {
        if (is_array($key)) {
            $fldArray = $key;
        } elseif (is_string($key)) {
            $fldArray  = [$key => $value];
        } else {
            $fldArray = [];
        }
        
        foreach ($fldArray as $key => $value) {
            $key = (substr($key, 0, 1) == '{' ? $key : '{'.$key);
            $key = (substr($key, -1) == '}' ? $key : $key. '}');
            $this->contextFields[$key] = $value;
        }
    }    

/******************************************************************************
    getContextFields($key)
    Возвращает значение по ключу $key (за пределы класса, т.к. внутри можно брать напрямую)
    
    Параметры:
        $key -  строка (ключ)
                массив ключей
        
    Возвращаемое значение:
        Строка, значение поля, если передан один ключ, пустая строка, если не найдено
        Массив значений, если передан массив ключей
*/
    public function getContextFields($key)
    {        
        if (is_array($key)) {
            $result = array_fill_keys($key, '');
        } else if (is_string($key)) {
            $result = [$key => ''];
        } else {
            $result = [];
        }
        
        foreach ($result as $k => $v) {
            $result[$k] = (isset($this->contextFields[$k]) ? $this->contextFields[$k] : '');
        }
        
        return (count($result) == 0 ? '' : (count($result) == 1 ? $result[$key] : $result));
    }  
    
/******************************************************************************
    getWorkingMode()
    Возвращает текущее значение $this->workingMode (нужно только для "наружу")
    
    Параметры:
        Нет
        
    Возвращаемое значение:
        Строка, $this->workingMode
*/
    public function getWorkingMode()
    {        
        return $this->workingMode;
    }
    
/******************************************************************************
    loadTemplate($name = '')
    Загружает из файла $name (.htm) текст шаблона HTML-страницы
    
    Параметры:
        $name   -   строка, имя шаблона (соответствует имени режима из $this->workingMode)
                    строка, имя файла (оканчивается на '.htm')
                    пустая строка, тогда берётся $this->workingMode
        
    Возвращаемое значение:
        Строка, текст шаблона, если файл найден
        Пустая строка, если что-то не получилось (хотя можно было бы и false отдать, хз-хз)
*/
    private function loadTemplate($name = '')
    {        
        $template = '';
        if ($name == '') {                       // Имя не передано
            $name = $this->getBaseDir('templates') . $this->workingMode . '.htm';
        } elseif (substr($name, -4) != '.htm') { // Передано краткое имя шаблона
            $name = $this->getBaseDir('templates') . $name . '.htm';
        } else {                                 // Передано полное имя, действие не требуется 
            
        }
        
        if (file_exists($name)) {
            $template = file_get_contents($name);
        }
        
        return $template;
    }
    
/******************************************************************************
    extractFieldNames($template)
    Загружает из текста шаблона названия полей
    
    Параметры:
        $template   -   строка, текст шаблона (HTML со вставками {ПОЛЕЙ})
                    
    Возвращаемое значение:
        Массив с названиями полей, включая скобки (одномерный)
*/
    private function extractFieldNames($template)
    {   
        $result = [];
        if (preg_match_all("|{[A-Za-zА-Яа-я0-9\-\.\+\:\=\s]+}|U", $template, $result, PREG_PATTERN_ORDER) > 0) {
            $result = $result[0];
        }
        return $result;
    }    
    
/******************************************************************************
    render()
    Формирует из имеющихся в объекте класса данных и заранее загруженного шаблона
    итоговый HTML-код страницы, которая будет выведена пользователю в браузер.
    За вывод в браузер отвечает вызывающая сторона.
    
    Параметры:
        Нет
        
    Возвращаемое значение:
        Строка, итоговый HTML-код страницы
*/    
    public function render()
    {
        $this->currentTemplate = $this->loadTemplate();                     // Получим шаблон, соответствующий текущему режиму
        $result = strtr($this->currentTemplate, $this->contextFields);      // Проставим в него заранее вычисленные поля
        
        $this->mergeFields = $this->extractFieldNames($this->currentTemplate);// Также получим полный перечень ожидаемых полей
        $result = strtr($result, array_fill_keys($this->mergeFields, ''));  // И удалим их из шаблона (хотя так стоит делать только в продакшн)
                            
        return $result;
    }
}