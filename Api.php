<?php
	/*Arquivo PHP onde será utilizado as duas API
	1ª será usada para verificar a temperatura da cidade
	2ª Retornará um tipo de pokemon*/

	// Variavei globais
	$tipo;
	$selecao;
	$data_pokemon;
	$data = array();
	$pokemons = array();
	$url_poke = array();

	$cidades = $_POST["sCidade"];

	if (!empty($_POST["sAux"])) {
		$sAux = $_POST["sAux"];	
	}

	function return_data_pokemon(){
		global $selecao, $data_pokemon, $url_poke;

		$ApiUrl = $url_poke[$selecao];

		$json = curl_init();

		curl_setopt($json, CURLOPT_HEADER, 0);
		curl_setopt($json, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($json, CURLOPT_URL, $ApiUrl);
		curl_setopt($json, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($json, CURLOPT_VERBOSE, 0);
		curl_setopt($json, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($json);

		curl_close($json);
		$data_pokemon = json_decode($response);			
	}
	
	// Api PokeApi
	function return_pokemon() {
		global $pokemons, $url_poke, $tipo, $selecao, $data, $sAux;

		$ApiUrl = "https://pokeapi.co/api/v2/type/".$tipo;

		$json = curl_init();

		curl_setopt($json, CURLOPT_HEADER, 0);
		curl_setopt($json, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($json, CURLOPT_URL, $ApiUrl);
		curl_setopt($json, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($json, CURLOPT_VERBOSE, 0);
		curl_setopt($json, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($json);

		curl_close($json);
		$data = json_decode($response);	

		foreach ($data->pokemon as $lista) {
			$nome = $lista->pokemon->name;
			$url = $lista->pokemon->url;
			array_push($pokemons, $nome);
			array_push($url_poke, $url);
		}

		do {

			$selecao = array_rand($pokemons);

			if ($selecao == $sAux) {
				unset($pokemons[$selecao]);
				unset($url_poke[$selecao]);
			}

		} while ($selecao == $sAux);

		$sAux = $selecao;

		return_data_pokemon();
	}

	// ApiWeatherMap
	function return_clima() {
		global $cidades, $tipo;

		$ApiUrl = "https://api.openweathermap.org/data/2.5/weather?q=".$cidades."&appid=fa8db2d4aa3316723795b6f474fe738e&units=metric";
		
		$json = curl_init();

		curl_setopt($json, CURLOPT_HEADER, 0);
		curl_setopt($json, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($json, CURLOPT_URL, $ApiUrl);
		curl_setopt($json, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($json, CURLOPT_VERBOSE, 0);
		curl_setopt($json, CURLOPT_SSL_VERIFYPEER, false);
		$response = curl_exec($json);

		curl_close($json);
		$data = json_decode($response);

		if ((isset($data->weather[0]->main)) || ($cidades == "")){
			$clima = $data->weather[0]->main;

			if ($clima == "rain") {
				$tipo = electric;
			} else {
				$celsius = $data->main->temp;

				if ($celsius < 5) {
					$tipo = 'ice';
				} else if (($celsius >= 5) && ($celsius < 10)) {
					$tipo = 'water';
				} else if (($celsius >= 12) && ($celsius < 15)) {
					$tipo = 'grass';
				} else if (($celsius >= 15) && ($celsius < 21)) {
					$tipo = 'ground';
				} else if (($celsius >= 23) && ($celsius < 27)) {
					$tipo = 'bug';
				} else if (($celsius >= 27) && ($celsius <= 33)) {
					$tipo = 'rock';
				} else {
					$tipo = 'fire';
				}
			}

			return_pokemon();
		} else {
		echo ('<script> alert("Cidade não encontrada, ou não informada!"); location="./index.html" </script>');
		}		
	}

	
	// Executa funções principais
	return_clima();
?>

<html>
	<head>
		<meta charset="utf-8">
		<title> Pokemon </title>
		<link rel="stylesheet" type="text/css"  href="CSS/style_css.css" />
	</head>

	<body>
		<div id="principal">
			<div id="banner">
				<img src="Image/logo.png" height="75px">				
			</div>

			<div id="salto"></div>

			<div id="exibicao">
				<table border="1px solid black">
					<tr>
						<td width="20%"> Nome: </td>
						<td width="20%"> <?php echo($data_pokemon->name) ?> </td>
						<td rowspan="5"> 
							<?php 
								$image = $data_pokemon->sprites->front_default;					
								$image_pokemon = 'Image/'.$data_pokemon->name.'.png';
								$ch = curl_init($image);
								$fp = fopen($image_pokemon, 'wb');
								curl_setopt($ch, CURLOPT_FILE, $fp);
								curl_setopt($ch, CURLOPT_HEADER, 0);
								curl_exec($ch);
								curl_close($ch);
								fclose($fp);
								echo ('<img src="'.$image_pokemon.'" width="100%" height="auto">');
							?>							
						</td>
					</tr>

					<tr>
						<td width="20%"> Tipo: </td>
						<td width="20%"> <?php echo($tipo)?> </td>
					</tr>

					<tr>
						<td width="20%"> Peso / Altura: </td>
						<td width="20%"> 
							<?php 
								$peso = ($data_pokemon->weight / 10);
								echo ($peso);
								echo (' kg / ');
								$altura = ($data_pokemon->height / 10); 
								echo ($altura);
								echo (' m');
							?> 
						</td>
					</tr>

					<tr>
						<td width="20%"> Fraqueza: </td>
						<td width="20%">
							<?php 
								foreach ($data->damage_relations->double_damage_from as $lista) {
									echo ($lista->name);
									echo ('<br>');
								}
							?> 
						</td>
					</tr>

					<tr>
						<td width="20%"> Ataques: </td>
						<td width="20%">
							<?php 
								foreach ($data_pokemon->moves as $lista) {
									echo ($lista->move->name);
									echo ('<br>');
								}
							?> 
						</td>
					</tr>
				</table>
			</div>

			<div id="salto"></div>

			<div id="exibicao">
				<form method="post" action="Api.php">
					<table>
						<tr>
							<td align="center">
								<input type="hidden" name="sAux" 	value="<?php echo $sAux?>">
								<input type="hidden" name="sCidade" value="<?php echo $cidades?>">
								<input type="image" alt="Submit" src="Image/button.png" width="45px" height="45px">
							</td>
							<td/>
							<td align="center"> <a href="Index.html"> Home </td>
						</tr>
					</table>	
				</form>			
			</div>
		</div>
	</body>
</html>