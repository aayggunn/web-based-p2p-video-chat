<?php
	$host = "localhost";
	$kullanici = "eruluden_aygun";
	$parola = "ayGun!6751";
	$vt = "eruluden_kayeka";

	$baglanti = @mysqli_connect($host, $kullanici, $parola, $vt);
	mysqli_set_charset($baglanti, "UTF8");

	if(mysqli_connect_errno()>0){
		die("Error: ". mysqli_connect_errno());
	}
?>
