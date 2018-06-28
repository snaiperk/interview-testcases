<?
	/*  phpTest
	 *  Данного интерфейса будут придерживаться все тесты, в которых это будет возможно.
	 *  Это поможет максимально унифицировать демонстрацию тестовых заданий, при этом не дублируя UI
	 */
	interface phpTest{
		public function getCompanyName();
		public function configTestForm();
		public function getResultMarker();
		public function getComments();
		public function computeResults($args_object);
	}
	
	/*  BasicPhpTest
	 *  Чем больше я смотрю, тем больше недоумеваю. Почему методы, определённые в суперклассе, не хотят видеть данные, 
	 *  статически определённые в классе-наследнике? Можно, конечно, списать это на особенности языка, но гораздо правильнее
	 *  назвать такое поведение словом из трёх букв (баг). Собственно, именно это обстоятельство помешало в полной мере поиметь
	 *  те выгоды, которые, по идее, должно давать наследование, а именно - избегание дублирования кода.
	 */
	abstract class BasicPhpTest{
		private $companyName;
		private $comments;
		private $fileName;
		private $resultMarker	= 'needToCompute';
		
		public function configTestForm(){
			$form = ['name'=>"Название теста", 
					'fields'=>[
						'inputString'=>['type'=>'edit', 'caption'=>'Введите строку', 'useDefault'=>'Входная строка', 'newline'=>1],
						$this->resultMarker=>['type'=>'submit', 'caption'=>'', 'value'=>[/*'Вычислить (BIN FORMAT)!',*/ 'Вычислить!']]
					]];			
			return $form;
		}
	}
	
	/*  phpTestDirector
	 *  Синглтон, отвечающий за отображение конкретного текущего теста
	 *  Возможно, в будущем немного изменю иерархию include-ов, с целью сделать "главным" файл интерфейса, а тесты - модулями.
	 */
	class phpTestDirector{
		private $curTest;
		private $company 	= 	'';
		private $comments 	=	'';
		private $testForm 	=	'';
		private $testFormCode =	'';
		private $resultMarker = "### NOTHING TO DO HERE ###";
		private $testResult = "";
		private $testResultPrefix = "";
		private static $testDirector = NULL;
		
		private function __construct(phpTest $test){
			$this->curTest 		= 		$test;
			$this->company 		= 		$this->curTest->getCompanyName();
			$this->comments		= 		$this->curTest->getComments();
			$this->testForm		=	 	$this->curTest->configTestForm();
			$this->testFormCode	=		$this->ProcessTestForm();
			$this->resultMarker = 		$this->curTest->getResultMarker();
			if(isset($_POST[$this->resultMarker])){
				$this->testResultPrefix = "<h2>Результат вычислений:</h2>\n";
				$this->testResult = $this->curTest->computeResults($_POST);
			}
		}
		
		private function defaultValue($arg_name, $defaultVal = 0){
			return (isset($_POST[$arg_name])?$_POST[$arg_name]:$defaultVal);
		}		
		
		private function ProcessTestForm(){
			$template = "<b>Называется \"".$this->testForm['name']."\"</b> <br> <form method='post'>\n";
			foreach($this->testForm['fields'] as $field => $properties){
				$inputPrefix = "{$properties['caption']} <input type='{$properties['type']}' name='$field' autocomplete='off' value='";
				if(isset($properties['value'])){
					if(is_array($properties['value'])){
						foreach($properties['value'] as $num => $value){
							$template .= "$inputPrefix$value' />\n";
						}
					}else 
						$template .= "$inputPrefix$value' />\n";
				}
				else
					$template .= "$inputPrefix{$this->defaultValue($field, $properties['useDefault'])}' />\n";
				
				if(isset($properties['newline']))
					$template .= str_repeat('<br />', $properties['newline']);
			}
			$template .= "</form>";
			return $template;
		}
		
		public static function getInstance(phpTest $test){
			if(!phpTestDirector::$testDirector)
				phpTestDirector::$testDirector = new phpTestDirector($test);
			return phpTestDirector::$testDirector;
		}
		
		public function render(){
	?>
<html>
	<head>
		<meta charset='utf-8' />
		<title>Задание от компании <?=$this->company?></title>
	<head>
	<body style='background-color: #F0FFF0'>
		<h1>Тестовое задание от компании <?=$this->company?></h1>
		Вернуться <a href='../index.php'>на главную</a><hr>
		<?=$this->testFormCode?>
		<hr>
		<?=(strlen($this->comments)>0?"<b>Комментарии к тесту:</b><br>\n$this->comments<hr>\n":"")?>
		<?=$this->testResultPrefix.$this->testResult?>
	</body>
</html>
	<?	
		}
	}
?>