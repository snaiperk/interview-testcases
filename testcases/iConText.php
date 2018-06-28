<?
/*
*** ИСХОДНЫЙ ТЕКСТ ЗАДАНИЯ ***

	Введение
	Есть 36 ячеек (ноль не считаем) и 18 фишек. В одну ячейку можно положить только одну фишку. Пример разложения:
	1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 19 20 21 ... 36
	$ $ $ $ $ $ $ $ $ $  $  $  $  $  $  $  $  $
	Нужно найти и сложить в тестовый файл все возможные варианты таких разложений.

	Задача
	Вход - два целых числа: fieldsCount - количество ячеек, chipCount - количество фишек (нужен какой-то интерфейс). 
	Требуется предоставить все возможные способы расстановки всех фишек по ячейкам. В одну ячейку можно положить только одну фишку.
	Выход - текстовый файл, в первой строке указывающий число вариантов, а далее содержащий все подходящие варианты. 
	Если вариантов менее 10, файл должен содержать только текст "Менее 10 вариантов". 
	Приветствуется самый быстрый и функциональный (протестированный относительно входных данных) вариант.

*** МОЙ КОММЕНТАРИЙ ***
	Задача расстановки фишек является частным случаем Сочетаний. Что ж, тряхнём школьной комбинаторикой!
	Количество сочетаний вычисляется по формуле С(n, k) (Цэ из эн по ка, звучит как-то по украински) = n!/k!(n-k)!
	Есть один занятный алгоритм для вычисления этого биномиального коэффициента...
	Далее идёт перебор самих комбинаций, думаю сделать его рекурсивным
	Сильно извращаться на предмет автоматизации форм не будем, т.к. надо же меру знать-то!
	Итак, поехали!
*/
    // Эмпирически задаём ограничение для входных данных, исходя из возможностей нашего сервера и здравого смысла, 
	const FIELDS_LIMIT = 36; // Даже при таких небольших количествах ячеек разложений уже получается что-то многовато :(
	const COMBINATION_RENDER_LIMIT = 1000;  // В принципе, можно было бы не лимитировать, но эта штука растёт очень быстро!
	const COMBINATION_RENDER_MIN   = 10;	// Ниже этого количества вариантов в файле будет плейсхолдер
	const COMBINATION_DROP_INTERVAL= 100000;// Если комбинаций больше, чем здесь указано, они будут сбрасываться на диск такими кусками
	require_once("../interface.php");

	// *** Конкретный класс теста ***
	class ChipComposer extends BasicPhpTest implements phpTest{
		private $outBuffer		=	'';
		private $fileBuffer		=	'';
		private $outBufferLen	=	0;
		private $fileBufferLen	=	0;
		private $chipSymbol		=	'$';
		private $spaceSymbol	=	'.';
		private $spaceString	= 	'';
		private $arr			=	[];
		private $companyName	= 'iConText';
		private $comments		= 'Изначально предлагались значения 36 и 18. Несмотря на теоретическую возможность текущего алгоритма вычислить такое количество сочетаний, настоятельно не рекомендую запускать скрипт на больших значениях.';
		private $fileName		= 'ChipComposer.txt';
		private $resultMarker	= 'needToCompute';		

		public function getResultMarker()	{return $this->resultMarker;}
		public function getCompanyName()	{return $this->companyName;}
		public function getComments()		{return $this->comments;}
		
		private function C($n, $k){ // Вычисление биномиального коэффициента
			// Не будем проверять типы и пределы здесь, так как функция приватная и кому попало не достанется
			$koef = 1;
			if ($n - $k > $k)
				$k = $n - $k;
			if($n != $k){
				for ($i = $k + 1; $i <= $n; $i++)
					$koef = $koef * $i;
				for ($i = 1; $i < ($n - $k + 1); $i++)
					$koef = $koef / $i;
			}
			return $koef;
		}

		// Составленную строку привести к формату хранения
		private function MakeStorageFormat($result, $trailing){
			if(is_int($result)){ // Размещаем результат на 5 байт для x64 и на 4 байта для x32-систем
				return 	chr($result & 0xFF).
						chr(($result & ((int)0xFF << 0x08))>>0x08).
						chr(($result & ((int)0xFF << 0x10))>>0x10).
						chr(($result & ((int)0xFF << 0x18))>>0x18).(PHP_INT_SIZE == 8?
						chr(($result & ((int)0xFF << 0x20))>>0x20):'');
			}
			else{
				return $result .str_repeat($this->spaceSymbol,$trailing). "\n"; 
			}
		}
		private function prepareNum($n){
			$result = $n.'';
			return str_repeat(' ',5-strlen($n)).$n;
		}
		
		private function NextSet($fields, $chips){
			$k = $chips;
			for($i = $k - 1; $i >= 0; --$i){
				if($this->arr[$i] < $fields - $k + $i + 1){
					++$this->arr[$i];
					for($j = $i + 1; $j < $k; ++$j)
						$this->arr[$j] = $this->arr[$j - 1] + 1;
					return true;
				}
			}
			return false;
		}
		
		private function FillRow($fields, $chips){
			$result = ''; 
			$j = 0;
			for($i = 0; $i < $fields; $i++){
				if($i == $this->arr[$j]){
					$result .=  '1';
					$j++;
				}
				else $result .= '0';
			}
			$this->fileBuffer .= $result . "\n";
			$this->fileBufferLen++;
			if($this->fileBufferLen >= COMBINATION_DROP_INTERVAL){
				$this->SaveResult();
				$this->fileBufferLen = 0;
			}
		}
		
		// Циклический метод на "обычной" арифметике. Хорош, но не лучший.
		private function Combine2($fields, $chips){
			ini_set("max_execution_time","0");
			for($i=0; $i < $fields; $i++){
				$this->arr[$i] = $i + 1;
			}
			$this->FillRow($fields, $chips);
			if($fields >= $chips){
				while($this->NextSet($fields, $chips)){
					$this->FillRow($fields, $chips);
				}
			}
			$this->SaveResult();
		}
		// Рекурсивный метод, возвращающий все варианты расстановок, доступные на каждом шаге
		// Худший из трёх возможных алгоритмов
		private function Combine(int $chips, int $fields, $prefix, int $level=0){
			$freeSpace = $fields - $chips; // Сколько вообще осталось места для расстановок		
			$tmp=(is_int($prefix)?(1 << $level):''); 
			
			for($i = 0; $i <= $freeSpace; $i++){
				// Первым делом получаем отступ текущей фишки и кладём её
				if(is_int($prefix)){
					$result = $prefix | ($tmp << $i);
				}
				else{
					$result = $prefix . $tmp . $this->chipSymbol;
					$tmp .= $this->spaceSymbol;
				}

				if($chips > 1) 			// Ещё не все фишки разложены, пока не стоит
					$this->Combine($chips-1, $fields-$i-1, $result, ++$level); // Дораскладываем
					// На этом месте я вынужден признаться, что затупил и долго передавал в рекурсивный вызов вычисленное свободное место
					//	вместо количества полей (что предполагается логикой кода). Самое интересное, что какое-то время это даже работало
				else
					if($chips == 1){ // Осталась последняя, и она на каждом текущем шаге цикла лежит на своём месте - то есть её можно уже выводить
						$this->fileBuffer	.= 	$this->MakeStorageFormat($result, $freeSpace-$i);
						$this->fileBufferLen++;
						if($this->fileBufferLen >= COMBINATION_DROP_INTERVAL)
							$this->SaveResult();
						
						if(is_string($prefix)){
							if($this->outBufferLen < COMBINATION_RENDER_LIMIT)
							{
								$this->outBuffer .= $this->prepareNum(++$this->outBufferLen).'. [ '.$result.str_repeat($this->spaceSymbol,$freeSpace-$i)." ]<br>\n";
							}
						}
					}
			}
		}
		
		private function checkInput($n){
			return (is_int($n) && ($n <= FIELDS_LIMIT) && ($n > 0) && ($n <= (PHP_INT_SIZE << 3))); // Вынужденное добавление в связи с переходом на бинарное хранение
		}
		
		private function ClearFile(){
			if(is_writable ($this->fileName)){
				file_put_contents($this->fileName, "");
				return true;
			}else return false;
			// Ошибками сыпать не будем, но можно
		}
		private function SaveResult(){
			if(is_writable ($this->fileName) || !file_exists($this->fileName)){
				$h = fopen($this->fileName, 'a+');
				fwrite($h, $this->fileBuffer);
				$this->fileBuffer = '';
				$this->fileBufferLen = 0;
				return true;
			}
			else return false;
		}
		public function computeResults($args){
			$result = 'Подготовка к вычислению';
			$output = '';
			$errlog = '';
			$n = intval($args['fieldsCount']); // Можно, конечно, проверить на существование в запросе, защититься от битого запроса... Но зачем тут?
			$k = intval($args['chipCount']);
			$option = $args[$this->resultMarker];
			$isBinary = strpos($option, 'BIN')!==false;
			if(!$this->checkInput($n)) $errlog .= 'Ошибка ввода количества ячеек, ожидается число от 1 до '.FIELDS_LIMIT.", а передано \"$n\"<br />\n";
			if(!$this->checkInput($k)) $errlog .= 'Ошибка ввода количества фишек, ожидается число от 1 до '.FIELDS_LIMIT.", а передано \"$k\"<br />\n";
			if($n < $k) $errlog .= "Ошибка ввода количества фишек - их больше, чем ячеек! Все не влезут, придётся складывать горкой.<br />\n";
			if($errlog == ''){
				$combinations = $this->C($n, $k);
				$variants = explode(',','ов,,а,а,а,ов,ов,ов,ов,ов');											/* 		:)		*/
				$result = 'Имеем '.number_format ( $combinations , 0, '.', ' ' ).' возможны'.($combinations % 10 != 1?'х':'й').' вариант'.$variants[$combinations%10].' расстановки!';
				$this->outBuffer	=	($combinations>200?'  Если транспонировать и анимировать эти строки, будет похоже на игру для винтажных мобильников, типа змейки или тетриса:':'  Сами комбинации:')."\n";
				if($combinations > COMBINATION_RENDER_LIMIT){
					$this->outBuffer .= "(на экран влезло не всё)\n";
				}
				$this->fileBuffer	=	$combinations."\n";
				$this->outBufferLen	=	0;
				$this->fileBufferLen=	0;
				$this->ClearFile();
				$this->spaceString = str_repeat($this->spaceSymbol, $n); // Получаем строку из кучи пробелов (это уже не надо, так как я грохнул текстовый функционал и перешёл на бинарный)
				$this->Combine($k, $n, ($isBinary?0:'')); // Вычисляем, даже если вариантов меньше - в файле будет вариант согласно заданию, а на экране будет красота
				//$this->Combine2($n, $k); // Метод потенциально крутой, но работает через жёпъ
				// Хотя это совершенно не обязательно, можно спрятать вызов вычислителя в условие.
				$fileUrl = "<a href='$this->fileName' download>файл</a>";
				if($combinations > COMBINATION_RENDER_LIMIT){
					$this->outBuffer .= "Превышен лимит отображения комбинаций. Показаны первые ".COMBINATION_RENDER_LIMIT." штук.<br>\nВ $fileUrl всё записалось нормально, не переживайте.";
				}
				if($combinations < COMBINATION_RENDER_MIN){
					$this->fileBuffer	=	'Менее '.COMBINATION_RENDER_MIN.' вариантов';
				}
				$this->SaveResult(); // В суматохе чуть не забыли о самом главном - отчитаться о результате
				// Маленькая ремарочка, про вывод чисел в задании ничего не было сказано, но если они нужны - можно и добавить
				$result .= " Скачайте ".($isBinary?'бинарный':'текстовый')." $fileUrl с результатом, без регистрации и смс!<hr>\n<pre>\n$this->outBuffer\n</pre>";
			}
			else{
				$result = "<b>В процессе работы возникли ошибки:</b><br>\n$errlog";
			}
			
			return $result;
		}		
		
		public function configTestForm(){
			// Я решил для начала не разделять полностью отображение и логику, хотя это немножко напрашивается
			$form = ['name'=>"Про ячейки и фишки", 
					'fields'=>[
						'fieldsCount'=>['type'=>'number', 'caption'=>'Введите количество полей', 'useDefault'=>10, 'newline'=>1], /* 36 */
						'chipCount'=>['type'=>'number', 'caption'=>'Введите количество фишек', 'useDefault'=>5, 'newline'=>1],    /* 18 */
						$this->getResultMarker()=>['type'=>'submit', 'caption'=>'', 'value'=>[/*'Вычислить (BIN FORMAT)!',*/ 'Вычислить!']]
					]];
			return $form;
		}
	}
	//***** НАЧАЛО РАБОТЫ ТУТА *****
	// Создадим экземпляры директора, отвечающего за отображение, и теста, отвечающего за логику теста
	$director = phpTestDirector::getInstance(new ChipComposer());
	// И выведем шаблон на экран. То, что должно было быть посчитано, было посчитано ещё в конструкторе директора.
	$director->render();
?>