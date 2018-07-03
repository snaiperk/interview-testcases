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
    private const MARKER_WORKING_MODE = 'phpTestDirector_workingMode';
    private const MARKER_CLASSNAME    = 'phpTestDirector_currentTest';
    
    private static $testDirector    = null; // Объект-одиночка
    
    // Директории, поля замены, поля ввода
    private $baseDir                = [];   // Массив базовых директорий
    private $contextFields          = [];   // Массив полей подстановки
    private $mergeFields            = [];   // Массив всех полей, найденных в шаблоне
    private $testForm               = [];   // Массив полей для ввода данных в тест
    // Режим работы
    private $workingMode            = '';   // Текущее "рабочее состояние" приложения (мы ищем и выводим тесты, показываем один тест или считаем его)
    private $workingModeDefault     = 'select-testcase'; // Режим работы "по умолчанию"
    // Шаблон
    private $currentTemplate        = '';   // Текущий шаблон отображения, текст
    // Объекты тестов:
    private $curTest;                       // Текущий тест, который мы показываем или считаем
    private $phpTestCases           = [];   // Массив найденных решений
    
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
    Выполняет инициализацию объекта.
    Тут пока что ничего делать не нужно, можем отдохнуть. Ура!
    
    Параметры:
        нет
        
    Возвращаемое значение:
        Конструктор, значений не возвращает
*/
    private function __construct()
    {
    }
    
/******************************************************************************
    doSomeLogic()
    Реализует основную логику класса - решает "что делать" и делает это!
    По идее, должно вызываться из метода $this->render()
    
    Параметры:
        Нет
        
    Возвращаемое значение:
        Нет
*/        
    private function doSomeLogic()
    {
        // 1. Определим текущий режим работы
        $this->workingMode = $this->defaultValue(phpTestDirector::MARKER_WORKING_MODE, $this->workingModeDefault);
        
        // 2. Определим, передано ли имя класса, и если да - создадим соответствующий объект
        $tmpName = $this->defaultValue(phpTestDirector::MARKER_CLASSNAME, '\ArrayObject');
        if ($tmpName != '\ArrayObject') $this->curTest = new $tmpName; // А если нет - то он так и будет null
        
        // 3. Определим для себя алгоритм работы в каждом режиме
        switch ($this->workingMode) {
            case 'select-testcase':
                $this->scanTestCaseDir(); // Загрузим список классов
                $this->addContextFields([
                    'ФОНОВОЕ ИЗОБРАЖЕНИЕ' => 'background-bricks-01.jpg',
                    'ФОРМА ВЫБОРА ЗАДАНИЙ' => $this->makeForm('cases-list'),
                    'КОЛИЧЕСТВО ЗАДАНИЙ' => count($this->phpTestCases) // В шаблоне не предусмотрено 0 и др. значения, но допилить недолго
                ]);
                break;
            case 'view-test':
                $this->addContextFields([
                    'ФОНОВОЕ ИЗОБРАЖЕНИЕ' => 'background-mosaic-01.jpg',
                    'КОМПАНИЯ' => $this->curTest->getCompanyName(),
                    'КОММЕНТАРИИ К ТЕСТУ' => $this->curTest->getComments('input-data'),
                    'ФОРМА ВВОДА' => $this->makeForm('input-data')
                ]);
                break;
            case 'view-test-results':
                $this->addContextFields([
                    'ФОНОВОЕ ИЗОБРАЖЕНИЕ' => 'background-mosaic-02.jpg',
                    'КОМПАНИЯ' => $this->curTest->getCompanyName(),
                    'РЕЗУЛЬТАТ ВЫЧИСЛЕНИЙ' => $this->makeForm('test-result'),
                    'КОММЕНТАРИИ К РЕЗУЛЬТАТУ' => $this->curTest->getComments('test-result')
                ]);
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
    makeForm($formType)
    Формирует из массива полей объекта phpTestCase HTML-код для вставки в шаблон
    Форма для ввода исходных данных на стадии запуска теста
    
    Параметры:
        $formType - строка, тип запрошенной формы
        
    Возвращаемое значение:
        Строка, HTML-код формы для вставки в шаблон
*/     
    private function makeForm($formType)
    {
        $template = '';
        switch ($formType) {
            case 'cases-list':
                $template      = "<form method='post'>\n";
                $template     .= "<input type='hidden' name='".phpTestDirector::MARKER_WORKING_MODE."' value='view-test'><table>\n";
                foreach ($this->phpTestCases as $testCase) {
                    $template .= "\t<tr>\n";
                    $template .= "\t\t<td>Тест \"".$testCase->getTestName()."\"</td>\n";
                    $template .= "\t\t<td>от компании \"<b>".$testCase->getCompanyName()."</b>\"</td>\n";
                    $template .= "\t\t<td><button type='submit' name='".phpTestDirector::MARKER_CLASSNAME."' value=\"".get_class($testCase)."\" >Смотреть</button></td>\n";
                    $template .= "\t</tr>\n";
                }
                $template .= "</table>\n</form>";
                break;
            case 'input-data':
                if ($this->is_testCase($this->curTest)) {
                    $this->testForm = $this->curTest->configTestForm();
                    $template  = "<h2>Задание называется \"".$this->testForm['name']."\"</h2> <form method='post'>\n";
                    $template .= "<input type='hidden' name='".phpTestDirector::MARKER_CLASSNAME."' value='".get_class($this->curTest)."'>\n";
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
                    $template .= "<hr>";
                    $template .= "<button type='submit' name='".phpTestDirector::MARKER_WORKING_MODE."' value='view-test-results'>Вычислить</button> ИЛИ \n";
                    $template .= "<button type='submit' name='".phpTestDirector::MARKER_WORKING_MODE."' value='select-testcase' >Вернуться к списку тестов</button><br />\n";                    
                    $template .= "</form>";
                } else {
                    $template  = '<h2>Увы, у нас возникла маленькая проблема</h2>\n';
                    $template .= 'Текущий тест, к сожалению, не инициализирован. Надо отлаживать, как такое получилось.';
                    $template .= 'Рекомендую начать с формы cases-list, которая, скорее всего, не передала имя класса теста.';
                }
                break;
            case 'test-result':
                if ($this->is_testCase($this->curTest)) {
                    $template  = '<form method="post">';
                    $template .= "<input type='hidden' name='".phpTestDirector::MARKER_CLASSNAME."' value='".get_class($this->curTest)."'>";                    
                    $template .= "<button type='submit' name='".phpTestDirector::MARKER_WORKING_MODE."' value='view-test'>Запустить тест заново</button> или ";
                    $template .= "<button type='submit' name='".phpTestDirector::MARKER_WORKING_MODE."' value='select-testcase' >Вернуться к списку тестов</button>";
                    $template .= '</form>';
                    $template .= '<hr> <br><pre style="width: 100%;height:200px;overflow:scroll;">' . $this->curTest->computeResults($_REQUEST) . '</pre>';
                } else {
                    $template  = '<h2>Увы, у нас возникла маленькая проблема</h2>\n';
                    $template .= 'Текущий тест, к сожалению, не инициализирован. Надо отлаживать, как такое получилось.<br />\n';
                    $template .= 'Рекомендую начать с формы input-data, которая, скорее всего, не передала имя класса теста.';
                }
                break;
            default:
            
        }
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
        if (preg_match_all(
                "|{[A-Za-zА-Яа-я0-9\-\.\+\:\=\s]+}|U", 
                $template, 
                $result, 
                PREG_PATTERN_ORDER
            ) > 0) {
            $result = $result[0];
        }
        return $result;
    }
    
/******************************************************************************
    is_testCase($object)
    Проверяет, реализует ли объект интерфейс теста PHP
    
    Параметры:
        $object   - объект произвольного класса
                    
    Возвращаемое значение:
        Boolean
*/
    private function is_testCase($object)
    {   
        return ($object instanceof phpTestCase);
    }  
    
/******************************************************************************
    scanTestCaseDir()
    Ищет классы, реализующие интерфейс phpTestCase, в директории кейсов
    Найденные классы помещает в $this->phpTestCases
    
    Параметры:
        Нет
                    
    Возвращаемое значение:
        Нет
*/
    private function scanTestCaseDir()
    {   
        if (false !== $dir = opendir($this->getBaseDir('testcases'))) {
            while (false !== $file = readdir($dir)) {
                if ((substr($file, -4) == '.php') && (strlen($file) > 4)) {// Потенциально - новый класс!
                    try {
                        $tempName = '\\snaiperk\\interview\\testcases\\'.substr($file, 0, strlen($file) - 4);
                        $tempObject = new $tempName;
                        
                        if ($this->is_testCase($tempObject)) {
                            if (!is_array($this->phpTestCases)) {
                                $this->phpTestCases = [];
                            }
                            array_push($this->phpTestCases, $tempObject);  // Добавили новый образчик в коллекцию
                        }
                    } catch (\Exception $e) {                           // Ну - не прокатило!
                        
                    }
                }
            }
        }
    } 
    
/******************************************************************************
    render()
    Практически основной метод класса! Всё начинается с его явного вызова.
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
        $this->doSomeLogic();                                               // Здесь всё: режим работа, показ, вычисления...
        $this->currentTemplate = $this->loadTemplate();                     // Получим шаблон, соответствующий текущему режиму
        $result = strtr($this->currentTemplate, $this->contextFields);      // Проставим в него заранее вычисленные поля
        
        $this->mergeFields = $this->extractFieldNames($this->currentTemplate);// Также получим полный перечень ожидаемых полей
        $result = strtr($result, array_fill_keys($this->mergeFields, ''));  // И удалим их из шаблона (хотя так стоит делать только в продакшн)
                            
        return $result;
    }
}