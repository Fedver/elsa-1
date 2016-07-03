<?php
	
	include("test.setup.php");

	$test = new Test($mysqli, "full");

	$selectvalues = $test->getAllTests();

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>ELSA test</title>
		<script src='../js/jquery.min.js'></script>
		<script src='js/test.js'></script>
		<script src='../js/spin.js'></script>
		<script src='../js/jquery.spin.js'></script>
		<script src="../js/bootstrap.js"></script>
		<link rel="stylesheet" href="../css/bootstrap.css">
    </head>
    <body>
		<a href="results.php" target="_self">Vai ai risultati</a> || <a href="partial.php" target="_self">Vai ai test parziali</a> || <a href="../index.php" target="_self">Torna alla home</a>
		<form action="<?php $_SERVER['PHP_SELF']; ?>" method="post" style="margin: 0 auto; width: 100%; height: 300px; margin-top: 200px; text-align: center;">
			<h1>Test completo</h1>
			<h4>Il test viene effettuato attraverso il reperimento di synset da BabelNet, le cui categorie e domini vengono pesati per attribuire un peso. La traduzione avviene sfruttando di nuovo BabelNet, oppure Microsoft Translator nel caso il primo non fosse disponibile; i risultati sono poi confrontati con ABSTAT per il reperimento dei mapping.</h4>
			<select id="header" name="header" style="width: 700px; height: 40px; font-size: large;">
				<?php
						for ($i = 0; $i < count($selectvalues); $i++){
							echo "<option value='".$selectvalues['header'][$i]."' data-lang='".$selectvalues['lingua'][$i]."' data-id='".$selectvalues['id'][$i]."'>(".$selectvalues['titolo'][$i]." ".$selectvalues['lingua'][$i].") ".$selectvalues['header'][$i]."</option>";
						}

				?>
			</select><br />
			<input type="text" id="separator" name="separator" placeholder="Inserire separatore" style="width: 200px; height: 40px; font-size: large;" value=";"/><br />
			<!--<input type="text" id="delimiter" name="delimiter" placeholder="Inserire delimitatore" style="width: 200px; height: 40px; font-size: large;" value='"' /><br /> --->
			<input type="button" value="Avvia il test" style="width: 110px; height: 40px; font-size: large;" onclick="compute();"/>
		</form>
		<span style="font-size: large; margin-left: 50px;">Esito:</span><br /><br />
		<div style="font-size: large; margin-left: 50px;" id="target">
		Qui viene visualizzato l'esito
		</div>
		<div id="spinner_target"></div>
    </body>
</html>
