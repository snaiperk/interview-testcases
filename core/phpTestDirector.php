<?php

namespace snaiperk\interview\core;

/*  phpTestDirector
 *  Синглтон, отвечающий за отображение конкретного текущего теста
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

    }
}