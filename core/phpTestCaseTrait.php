<?php
namespace snaiperk\interview\core;

/*  phpTestCaseTrait
 *  (та часть комментария, которая ниже, уже deprecated: раньше вместо трейта был абстрактный класс)
 *  Чем больше я смотрю, тем больше недоумеваю. Почему методы, определённые в суперклассе, не хотят видеть данные, 
 *  статически определённые в классе-наследнике? Можно, конечно, списать это на особенности языка, но гораздо правильнее
 *  назвать такое поведение словом из трёх букв (баг). Собственно, именно это обстоятельство помешало в полной мере поиметь
 *  те выгоды, которые, по идее, должно давать наследование, а именно - избегание дублирования кода.
 */
trait phpTestCaseTrait
{
    private $companyName;
    private $comments;
    private $fileName;
    private $resultMarker    = 'needToCompute';
    
    public function configTestForm(){
        $form = ['name'=>"Название теста", 
                'fields'=>[
                    'inputString'=>['type'=>'edit', 'caption'=>'Введите строку', 'useDefault'=>'Входная строка', 'newline'=>1],
                    $this->resultMarker=>['type'=>'submit', 'caption'=>'', 'value'=>[/*'Вычислить (BIN FORMAT)!',*/ 'Вычислить!']]
                ]];            
        return $form;
    } 

    public function getResultMarker()
    {
        return $this->resultMarker;
    }
    
    public function getCompanyName()
    {
        return $this->companyName;
    }
    
    public function getComments()
    {
        return $this->comments;
    }
}