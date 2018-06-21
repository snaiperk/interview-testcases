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
		private $companyName	= "iConText";
		private $fileName		= 'ChipComposer.txt';
		private $resultMarker	= 'needToCompute';
		private $outBuffer		=	'';
		private $fileBuffer		=	'';
		private $outBufferLen	=	0;
		private $fileBufferLen	=	0;
		private $chipSymbol		=	'$';
		private $spaceSymbol	=	' ';
		private $spaceString	= 	'';
		
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
		//private function MakeCombination($prefix){
		//	if(is_int($prefix->d)
		//}
		// Рекурсивный метод, возвращающий все варианты расстановок, доступные на каждом шаге
		private function Combine(int $chips, int $fields, int $prefix=0, int $level=0){
			$freeSpace = $fields - $chips; // Сколько вообще осталось места для расстановок		
			$tmp = (1 << $level);
			for($i = 0; $i <= $freeSpace; $i++){
				// Первым делом получаем отступ текущей фишки и кладём её
				$result = $prefix  | ($tmp << $i);

				if($chips > 1) 			// Ещё не все фишки разложены, пока не стоит
					$this->Combine($chips-1, $fields-$i-1, $result, ++$level); // Дораскладываем
					// На этом месте я вынужден признаться, что затупил и долго передавал в рекурсивный вызов вычисленное свободное место
					//	вместо количества полей (что предполагается логикой кода). Самое интересное, что какое-то время это даже работало
				else
					if($chips == 1){ // Осталась последняя, и она на каждом текущем шаге цикла лежит на своём месте - то есть её можно уже выводить
						$this->fileBuffer	.= 	chr($result & 0xFF).
												chr(($result & ((int)0xFF << 0x08))>>0x08).
												chr(($result & ((int)0xFF << 0x10))>>0x10).
												chr(($result & ((int)0xFF << 0x18))>>0x18).
												chr(($result & ((int)0xFF << 0x20))>>0x20);
						$this->fileBufferLen++;
						if($this->fileBufferLen >= COMBINATION_DROP_INTERVAL)
							$this->SaveResult();
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
			if(is_writable ($this->fileName)){
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
			$option = intval($args[$this->resultMarker]);
			if(!$this->checkInput($n)) $errlog .= 'Ошибка ввода количества ячеек, ожидается число от 1 до '.FIELDS_LIMIT.", а передано \"$n\"<br />\n";
			if(!$this->checkInput($k)) $errlog .= 'Ошибка ввода количества фишек, ожидается число от 1 до '.FIELDS_LIMIT.", а передано \"$k\"<br />\n";
			if($n < $k) $errlog .= "Ошибка ввода количества фишек - их больше, чем ячеек! Все не влезут, придётся складывать горкой.<br />\n";
			if($errlog == ''){
				$combinations = $this->C($n, $k);
				$variants = explode(',','ов,,а,а,а,ов,ов,ов,ов,ов');											/* 		:)		*/
				$result = 'Имеем '.number_format ( $combinations , 0, '.', ' ' ).' возможны'.($combinations % 10 != 1?'х':'й').' вариант'.$variants[$combinations%10].' расстановки!';
				$this->outBuffer	=	($combinations>200?'Если транспонировать и анимировать эти строки, будет похоже на игру для винтажных мобильников, типа змейки или тетриса:':'Сами комбинации:')."\n";
				if($combinations > COMBINATION_RENDER_LIMIT){
					$this->outBuffer .= "(на экран влезло не всё)\n";
				}
				$this->fileBuffer	=	$combinations."\n";
				$this->outBufferLen	=	0;
				$this->fileBufferLen=	0;
				$this->ClearFile();
				$this->spaceString = str_repeat($this->spaceSymbol, $n); // Получаем строку из кучи пробелов (это уже не надо, так как я грохнул текстовый функционал и перешёл на бинарный)
				$this->Combine($k, $n); // Вы числяем, даже если вариантов меньше - в файле будет вариант согласно заданию, а на экране будет красота
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
				$result .= " Скачайте $fileUrl с результатом, без регистрации и смс!<hr>\n<pre>\n$this->outBuffer\n</pre>";
			}
			else{
				$result = "<b>В процессе работы возникли ошибки:</b><br>\n$errlog";
			}
			
			return $result;
		}		
		
		public function getResultMarker() {return $this->resultMarker;}
		public function getCompanyName() {return $this->companyName;}
		public function renderTestForm(){
			// Я решил для начала не разделять полностью отображение и логику, хотя это немножко напрашивается
			$form = "<b>Называется \"Про ячейки и фишки\"</b> <br> <form method='post'>
			Введите количество полей: <input type='number' name='fieldsCount' 	maxlength='3' value='{$this->defaultValue('fieldsCount', 36)}'/><br>
			Введите количество фишек: <input type='number' name='chipCount' 	maxlength='3' value='{$this->defaultValue('chipCount', 18)}'/><br>
			<input type='submit' name='{$this->resultMarker}' value='Вычислить!'/>
			</form>"; // <input type='submit' name='{$this->resultMarker}' value='Вычислить (TEXT FORMAT)!'/> - можно ещё сделать так, чтобы был только текст
			
			return $form;
		}
	}
	//***** НАЧАЛО РАБОТЫ ТУТА *****
	// Создадим экземпляры директора, отвечающего за отображение, и теста, отвечающего за логику теста
	$director = phpTestDirector::getInstance(new ChipComposer());
	// И выведем шаблон на экран. То, что должно было быть посчитано, было посчитано ещё в конструкторе директора.
	$director->render();
?>