<?php
//////////////////////////////////////////////////////////////////////////////
// *countDaysPeriod* - функция расчета рабочих дней в заданном периоде !с фильтрацией праздничных дней!
// возвращает массив, где 
// 1-е значение - кол-во рабочих дней в заданном интервале дат
// 2-е значение - массив всех рабочих дней в формате гггг-мм-дд
// 3-е значение - массив интервала рабочих дней начало-конец раб. смены в формате гггг-мм-дд|гггг-мм-дд
// входные переменые функции countDaysPeriod:
//@ $start_d - начальная дата в формате гггг-мм-дд
//@ $end_d - конечная дата в формате гггг-мм-дд, 
//@ $grafic - график работы в формате от 1-1 до 3-3 
/////////////////////////////////////////////////////////////////////////////
function countDaysPeriod($start_d, $end_d, $grafic) 
{
//разбивка начальной конечной даты на составляющие (год, месяц, дата)
	$d_s = explode("-",$start_d);
	$dg_g = $d_s[0];
	$dm_g = $d_s[1];
	$d_e = explode("-",$end_d);
// массив условных обозначейний сменных графиков
	$smeni=['1-1','1-2','1-3','2-1','2-2','2-3','3-1','3-2','3-3'];
// функция построения массива ответа родительской функции для всех графиков работы кроме пятидневки/шестидневки
	function genArr($start, $end, $year, $mounth, $smena='')
	{
		if(!empty($smena)){
			// блок генерации всех рабочих дней согласно параметра "смена"
			$smena = explode ("-", $smena);
			$rab = (int)$smena[0];
			$chill = (int)$smena[1];
			while ($start <= $end){
				if ($rab > 0 && $chill ==$smena[1]){
					if ($start<=9) $start = "0".$start;
					$result[] = "$year-$mounth-$start";
					$start++; $rab--; 
				}else{
					if($chill > 0){
						$chill--; $start++;
					}else{
						$chill = (int)$smena[1]; 
						$rab = (int)$smena[0];
					}
				}
			}
			// блок генерации интервалов рабочих дат по сменному графику
			$count = count($result);
			$id=0;
			$i=0;
			$end_id = $id+$rab;
			while($id<$count){
				$interval[]= $result[$id]."|".$result[$end_id];
				$id = $end_id+1;
				$end_id = $id+$rab;
			}
		}else{
			// блок генерации ответа для ненормированного графика работы
			while ($start <= $end){
				$result[] = "$year-$mounth-$start";
				$result_end = end($result);
				$interval[]= $result_end."|".$result_end;
				$start++;
			}
		}					
		// формируем и возвращаем ответный массив с данными
		$rdata['count'] = count($result); // кол-во рабочих дней в заданном промежутке
		$rdata['dates'] = $result; // массив данных дней в формате год-месяц-дата
		$rdata['interval'] = $interval;
		return $rdata;
	}	
// выходные дни недели для графика работы
	if($grafic=='5'){      // пятидневка
		$ignore = [6,7];
	}elseif($grafic=='6'){ // шестидневка
		$ignore = [7];
	}elseif(in_array($grafic, $smeni)){ // смены
		$smena = $grafic; 
		$ignore = 'smena';
	}elseif($grafic = '0'){ // ненормированный раб. день
		$ignore = 'nenorm';
	}
	else{
		var_dump('Неверно передан парамерт функции countDays');
	}
// подсчет выходных дней в зависимости от графика работы
	// при сменном графике работы
	if ($ignore == 'smena'){
		return genArr((int)$d_s[2], (int)$d_e[2], (int)$d_s[0], $d_s[1], $smena);	
	}
	// при ненормированном раб. дне
	if ($ignore == 'nenorm'){
		return genArr((int)$d_s[2], (int)$d_e[2], (int)$d_s[0], $d_s[1]);
	}
	// при пятидневке/шестидневке
	for ($d=$d_s[2]; $d<=$d_e[2]; $d++){
		$loop = strtotime("$d_s[0]-$d_s[1]-$d");
		$pnn = strftime("%u",$loop);
		if($ignore and !in_array($pnn,$ignore)){
			$result[] = "$d_s[0]-$d_s[1]-".strftime("%d",$loop);
		}
	}
// убираем выходные праздничные дни (без сокращенных)
	$calendar = simplexml_load_file('http://xmlcalendar.ru/data/ru/'.$d_s[0].'/calendar.xml');
	$calendar = $calendar->days->day;
	//все праздники за заданный год
	foreach( $calendar as $day ){
		$d = (array)$day->attributes()->d;
		$d = $d[0];
		$mounth = substr($d, 0, 2);
		$days = substr($d, 3, 2); 
		//все праздники в заданном месяце
		if ($mounth==$d_s[1]){
			//находим и удаляем праздничные дни из массива (не считая сокращеные дни)
			$pr_days = "$d_s[0]-$d_s[1]-$days";
			if( $day->attributes()->t == 1 && in_array($pr_days ,$result) ) {
				unset($result[array_search($pr_days,$result)]);
			}
		}
	}
// формируем ответ для пятидневной/шестидневной раб недели
	// строим новый массив интервала дат
	foreach ($result as $val){
		$interval[] = $val."|".$val; 
	}
	$rdata['count'] = count($result); // кол-во рабочих дней в заданном промежутке
	$rdata['dates'] = $result; // массив данных дней в формате год-месяц-дата
	$rdata['interval'] = $interval;
	return $rdata;	 
}
//запуск
print_r( countDaysPeriod('2021-03-01','2021-03-31','5') );

?>
