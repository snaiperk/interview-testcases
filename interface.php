<?
	/*  phpTest
	 *  Данного интерфейса будут придерживаться все тесты, в которых это будет возможно.
	 *  Это поможет максимально унифицировать демонстрацию тестовых заданий, при этом не дублируя UI
	 */
	interface phpTest{
		public function getCompanyName();
		public function renderTestForm();
		public function getResultMarker();
		public function computeResults($args_object);
	}
	
	abstract class BasicPhpTest{
		private $companyName = "Basic company";
		private $fileName = 'ChipComposer.txt';
		private $resultMarker = 'needToCompute';
		
		public function defaultValue($arg_name, $defaultVal = 0){
			return (isset($_POST[$arg_name])?$_POST[$arg_name]:$defaultVal);
		}
	}
	
	class phpTestDirector{
		private $curTest;
		private $company = "";
		private $testForm = "";
		private $resultMarker = "### NOTHING TO DO HERE ###";
		private $testResult = "";
		private static $testDirector = NULL;
		
		private function __construct(phpTest $test){
			$this->curTest 		= 		$test;
			$this->company 		= 		$this->curTest->getCompanyName();
			$this->testForm 	=	 	$this->curTest->renderTestForm();
			$this->resultMarker = 		$this->curTest->getResultMarker();
			if(isset($_POST[$this->resultMarker]))
				$this->testResult = $this->curTest->computeResults($_POST);
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
		<?=$this->testForm?>
		<hr>
		<?=$this->testResult?>
	</body>
</html>
	<?	
		}
	}
?>