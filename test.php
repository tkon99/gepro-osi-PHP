<?php
include("rooster.class.php");
$rooster = new rooster();
?>
<h1>Gepro-osi API demo</h1>
<h2>Klassen:</h2>
<?php
var_dump($rooster->getGroups());
?>
<hr>
<h2>Docenten:</h2>
<?php
var_dump($rooster->getTeachers());
?>
<hr>
<h2>Lokalen:</h2>
<?php
var_dump($rooster->getRooms());
?>
<hr>
<h2>Richtingen:</h2>
<?php
var_dump($rooster->getSections());
?>
<hr>
<h2>Leerlingen in Vwo 4:</h2>
<?php
$leerlingen = $rooster->getStudents("Vwo 4");
foreach($leerlingen[0] as $i=>$name){
	echo($leerlingen[1][$i]." - ".$rooster->getNameList($name)."<br>");
}
?>
<hr>
<?php
//Laten we de naam ook uit het rooster halen
$url = $rooster->getUrl("leerling", "Vwo 4", "120003525");
$naam = $rooster->getName($url);
?>
<h2>Rooster van <?php echo($naam); ?>:</h2>
<?php
var_dump($rooster->getSchedule($url));
?>
<hr>
<h2>Notities onder het rooster:</h2>
<?php
echo($rooster->getNotes());
?>