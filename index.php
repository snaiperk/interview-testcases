<!--
<?
/*
*					Главная страница
*	Здесь мы можем ознакомиться с перечнем тестовых заданий
*/ 
	//$dir = $_SERVER["DOCUMENT_ROOT"]."/testcases/";
	$dir = "testcases/";
	$testCases = [];
	
	if (is_dir($dir)){ echo('dir ok: '.$dir);
	  if ($dh = opendir($dir)){
		while (($file = readdir($dh)) !== false){
		  if(strtolower(substr($file, -4)) == '.php'){
			  $testCases[] = $file;
		  }
		  echo $file."\n";
		}
		closedir($dh);
	  }
	}else echo('not a dir: '.$dir);
	$exists = (count($testCases)? ":" : " отсутствуют :-(");
?>
-->
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
		<h2><a id='testcase-<?=$num?>' href='<?=$dir.$name?>'>Задание <?=$num?></a>, компания <?=substr($name, 0, strlen($name)-3)?></h2>
<?				
			}
		?>
	</body>
</html>