<?php

// countDays - функция расчета рабочих дней в заданном месяце

function countDays($y, $m, $grafic) 
{
// массив условных обозначейний сменных графиков
	$smeni=['1-1','1-2','1-3','2-1','2-2','2-3','3-1','3-2','3-3'];
// выходные дни недели для графика работы
	if($grafic=='5'){      // пятидневка
		$ignore = [6,7];
	}elseif($grafic=='6'){ // шестидневка
		$ignore = [7];
	}elseif(in_array($grafic, $smeni)){ // смены
		$smena = $grafic; 
		$ignore = 'smena';
	}elseif($grafic = '0')){ // ненормированный раб. день
		$ignore = 'nenorm';
	else{
		var_dump('Неверно передан парамерт функции countDays');
	}
// подсчет выходных дней в зависимости от графика работы
	$ndays = cal_days_in_month(CAL_GREGORIAN, $m, $y); // 28-31
	// при сменном графике работы
	if ($ignore == 'smena'){
		$smena = explode ("-", $smena);
		$c_rab = ceil(($ndays/($smena[0]+$smena[1]))*$smena[0]);
		return $smena[0]  . "/".$smena[1]."</br>" .$c_rab;
	}
	// при ненормированном раб. дне
	if ($ignore == 'nenorm'){
		return $ndays;
	}
	// при пятидневке/шестидневке
	for ($d=1; $d<=$ndays; $d++){
		$loop = strtotime("$y-$m-$d");
		$pnn = strftime("%u",$loop);	
		if($ignore and in_array($pnn,$ignore)){
			$result[] = strftime("%d",$loop);
		}
	}
// подсчет выходных праздничных дней (без сокращенных)
	$calendar = simplexml_load_file('http://xmlcalendar.ru/data/ru/'.$y.'/calendar.xml');
	$calendar = $calendar->days->day;
	//все праздники за текущий месяц
	foreach( $calendar as $day ){
		$d = (array)$day->attributes()->d;
		$d = $d[0];
		$mounth = substr($d, 0, 2);
		//echo $mounth."</br>"; 
		if ((int)$mounth==(int)$m){
			//не считая короткие дни
			if( $day->attributes()->t == 1 ) {
				(int)$result[]= substr($d, 3, 2);
			}
			// если это сокращенный день, то удаляем его из массива
			if( $day->attributes()->t == 2 ) {
				unset($result[array_search(substr($d, 3, 2),$result)]);
			}
		}
	}	
	$result = array_unique($result);
	$loop = strtotime("$y-$m-01");
	$c_norab = 0;
	
    do if($result && in_array(strftime("%d",$loop),$result)) $c_norab++;
    while(strftime("%m",$loop = strtotime("+1 day",$loop))==$m);
	$c_rab = $ndays-$c_norab;
    return $c_rab;
}
//запуск
echo countDays(2021,2,'3-2');

?>
