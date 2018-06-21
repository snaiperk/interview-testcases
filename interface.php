<?
	/*  phpTest
	 *  Данного интерфейса будут придерживаться все тесты, в которых это будет возможно.
	 *  Это поможет максимально унифицировать демонстрацию тестовых заданий, при этом не дублируя UI
	 */
	interface phpTest{
		public function getCompanyName();
		public function configTestForm();
		public function getResultMarker();
		public function computeResults($args_object);
	}
	
	abstract class BasicPhpTest{
		private $companyName = "Basic company";
		private $fileName = 'ChipComposer.txt';
		private $resultMarker = 'needToCompute';
		
		public function configTestForm(){
			
		}
	}
	
	class phpTestDirector{
		private $curTest;
		private $company = "";
		private $testForm = "";
		private $testFormCode = "";
		private $resultMarker = "### NOTHING TO DO HERE ###";
		private $testResult = "";
		private static $testDirector = NULL;
		
		private function __construct(phpTest $test){
			$this->curTest 		= 		$test;
			$this->company 		= 		$this->curTest->getCompanyName();
			$this->testForm		=	 	$this->curTest->configTestForm();
			$this->testFormCode	=		$this->ProcessTestForm();
			$this->resultMarker = 		$this->curTest->getResultMarker();
			if(isset($_POST[$this->resultMarker]))
				$this->testResult = $this->curTest->computeResults($_POST);
		}
		
		private function defaultValue($arg_name, $defaultVal = 0){
			return (isset($_POST[$arg_name])?$_POST[$arg_name]:$defaultVal);
		}		
		
		private function ProcessTestForm(){
			$template = "<b>Называется \"".$this->testForm['name']."\"</b> <br> <form method='post'>\n";
			foreach($this->testForm['fields'] as $field => $properties){
				if(isset($properties['value']) && is_array($properties['value'])){
					foreach($properties['value'] as $num => $value){
						$template .= "{$properties['caption']} <input type='{$properties['type']}' name='$field' value='$value' />\n";
					}
				}
				else{
					if(isset($properties['value']))
						$template .= "{$properties['caption']} <input type='{$properties['type']}' name='$field' value='$value' />\n";
					else
						$template .= "{$properties['caption']} <input type='{$properties['type']}' name='$field' value='{$this->defaultValue($field, $properties['useDefault'])}' />\n";
				}
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
		<?=$this->testResult?>
	</body>
</html>
	<?	
		}
	}
?>