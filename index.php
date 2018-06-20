<?
/*
*					Главная страница
*	Здесь мы можем ознакомиться с перечнем тестовых заданий
*/

	$dir = "/testcases/";
	$testCases = [];
	// Open a directory, and read its contents
	if (is_dir($dir)){
	  if ($dh = opendir($dir)){
		while (($file = readdir($dh)) !== false){
		  if(strtolower(substr($file, -4)) == '.php'){
			  $testCases[] = $file;
		  }
		}
		closedir($dh);
	  }
	}
	$exists = (count($testCases)? ":" : " отсутствуют :-(");
?>
<html>
	<head>
		<meta charset='utf-8' />
		<title>Тестовые задания</title>
	<head>
	<body style='background-color: #F0FFF0'>
		<h1>Тестовые задания<?=$exists?></h1>
		<?
			if(!count($testCases)){
?>
				<h2></h2>
<?				
			}
			foreach($testCases as $num => $name){
?>
		<h2><a id='testcase-<?=$num?>' href='<?=$dir.$name?>'>Задание <?=$num?>: компания <?=substr($name, 0, strlen($name)-3)?></a> </h2>
<?				
			}
		?>
	</body>
</html>