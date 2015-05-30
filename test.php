<?php
include("rooster.class.php");
$rooster = new rooster();
// var_dump($rooster->getGroups());
// echo("<hr>");
// var_dump($rooster->getTeachers());
// echo("<hr>");
// var_dump($rooster->getRooms());
// echo("<hr>");
// var_dump($rooster->getSections());
// echo("<hr>");
// $leerlingen = $rooster->getStudents("Vwo 4");
// foreach($leerlingen[0] as $i=>$name){
// 	echo($leerlingen[1][$i]." - ".$rooster->getNameList($name)."<br>");
// }
$url = $rooster->getUrl("leerling", "Vwo 4", "120003525");
echo($rooster->getName($url));
echo(json_encode($rooster->getSchedule($url)));
//$rooster->getSchedule($rooster->getUrl("lokaal", "LA006"));