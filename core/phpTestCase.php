<?php
namespace snaiperk\interview\core;
/*  phpTestCase
 *  Данного интерфейса будут придерживаться все тесты, в которых это будет возможно.
 *  Это поможет максимально унифицировать демонстрацию тестовых заданий
 */
interface phpTestCase
{
    public function getTestName();
    public function getCompanyName();
    public function configTestForm();
    public function getComments($type);
    public function computeResults($argsObject);
}