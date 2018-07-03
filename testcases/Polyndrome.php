<?php
/*  ЗАДАНИЕ:
 *    Вход - строка
 *  Если строка является палиндромом - вернуть всю строку
 *  Если нет - вернуть палиндром наибольшей длины, содержащийся в строке
 *  Если палиндрома нет вообще - вернуть первую букву строки
 *
 *    МОЙ КОММЕНТАРИЙ:
 *    Возвращаю в виде сплошной строки, то есть исходные регистр и пробелы не восстанавливаю, спецсимволы из входа не удаляю.
 *  Строка "А Я Вас Савяа" вернётся в виде "АЯВАССАВЯА", и никак иначе. Хотя при необходимости, конечно, можно и сделать.
 */
namespace snaiperk\interview\testcases;

// *** Конкретный класс теста ***
class Polyndrome implements \snaiperk\interview\core\phpTestCase
{
    use \snaiperk\interview\core\phpTestCaseTrait;
    
	private $arr            =    []; // Сюда складываем все найденные результаты, а лучше всего...
	private $pos            = 0;    // ...будем тупо хранить позицию и...
	private $len            = 0;    // ...длину максимального палиндрома, и перезаписывать по необходимости!
	private $abba           = false;// Тип палиндрома на всякий случай тоже сховаем.
	private $sl             = 0;    // Длину строки сохраним в объекте, чтобы сэкономить на её вычислении
	private $inputStr       = '';   // Саму строку засунем туда же, чтобы не передавать каждый раз
	private $companyName    = 'TopHotel';
    private $testName       = 'Палиндром';
	private $comments       = [
        'input-data'  => '1. Для запуска этого теста в php.ini должно быть включено расширение mbstring (<b><span style="color:blue">extension</span>=<span style="color:green">mbstring</span>;</b>)<br>2. Построки вида "А" и "АА" палиндромами не считаются, ладно?',
        'test-result' => 'Без комментариев <img src="/images/smile-zip.png" width=32 height=32 />'
    ];
    
	private function getRefinedString($str)
	{
		return (is_string($str)?' '.mb_strtoupper(preg_replace('/\s+/', '', $str)).' ':'');
	}
	
	// Здесь НЕ защищаюсь от кривой адресации в угоду производительности
	// Могу себе это позволить, т.к. метод приватный
	private function charAt($pos)
	{
		return mb_substr($this->inputStr, $pos+1, 1);
	}
	
	// Ищем палиндромы двух видов: АБВБА и АББА
	private function polyndromLenAt($pos, &$is_abba)
	{
		$aba = $abba = true;
		for($len = 0; ($pos - $len >= 0) && ($pos + $len <= $this->sl + 1) && ($aba || $abba); $len++){
			$aba        &= $this->charAt($pos - $len) == $this->charAt($pos + $len);
			$is_abba     = $abba; // Защищаемся от "перетирания" при последнем холостом вызове
			$abba        &= ( ( $pos + $len + 1 <= $this->sl ) ? ( $this->charAt($pos - $len) == $this->charAt($pos + $len + 1) ) : false);
		}
		$is_abba |= $abba; // Если палиндром типа АББА занимает всю строку
		return $len - ($aba || $abba ? 0 : 1);
	}
	
	// Предопределённый интерфейсный метод класса
	public function computeResults($args)
	{      
		$this->inputStr = $this->getRefinedString($args['inputString']);
		$this->sl = mb_strlen($this->inputStr) - 2;
		$result = '';
		if($this->sl == 0)
			$result = '<span style="color:red;font-weight:bold">Внимание!</span> Передана пустая строка. <span style="color:green">Спасибо за внимание.</span>';
		else{
			$startPosition = floor($this->sl/2);
			
			$len1 = $len2 = 0;
			$abba1 = $abba2 = false;
			for($len = 0; ($startPosition - $len+1 >= $this->len) && ($startPosition + $len - 1 < $this->sl - $this->len); $len++){
				$len1 = $this->polyndromLenAt($startPosition - $len, $abba1);
				if($len > 0)$len2 = $this->polyndromLenAt($startPosition + $len, $abba2);
				if($this->len < $len1){
					$this->len = $len1;
					$this->abba = $abba1;
					$this->pos = $startPosition - $len;
				}
				if($this->len < $len2){ // Заодно и с "левой" веткой сравнили!
					$this->len = $len2;
					$this->abba = $abba2;
					$this->pos = $startPosition + $len;
				}
			}
            $this->comments['test-result'] = (($this->len > 1)?('Найден палиндром типа АБ'.($this->abba?'Б':'').'А, длиной '.$this->len.' символов.'):'В тексте палиндромов не найдено! Согласно заданию, возвращаем первую букву.');
			$result = (($this->len <= 1) ? mb_substr($this->inputStr, 1, 1) : mb_substr( $this->inputStr, $this->pos - $this->len+2, $this->len * 2 - ($this->abba?0:1)));
		}
		

		return $result;
	}

	public function configTestForm()
	{
		// Я решил для начала не разделять полностью отображение и логику, хотя это немножко напрашивается
		$form = ['name'=>"Про Аргентину, негра и палиндромы", 
				'fields'=>[
					'inputString'=>['type'=>'edit', 'caption'=>'Введите строку', 'useDefault'=>'Аргентина манит негра', 'newline'=>1]
				]];            
		return $form;
	}
}