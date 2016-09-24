<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Orabig CG stats</title>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
	<!-- JQuery -->
	<script src="https://code.jquery.com/jquery-3.1.1.min.js"   integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="   crossorigin="anonymous"></script>
	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
	<link rel="stylesheet" href="css/styles.css">
	<script src="js/jquery.query-object.js"></script>
  </head>
  <body>
 
  

  <div class="row">
	  <div class="col-md-12">
	  </div>
  </div>
  <div class="row">
	<div class="col-md-1">
		<!--LEFT-->
	</div>
	<div class="col-md-8">

		<!--    TITRE     -->
		<div class="page-header">
		  <h1><a href="?" class="btn"><span class="glyphicon glyphicon-home"/></a> CodinGame Puzzle Status</h1>
		</div>
	
		<?	
		$joueurID=$_GET["pid"];

		if (!$joueurID) {
			?>
			<form class="form-horizontal">
			  <div class="form-group">
				<label class="control-label col-sm-2" for="name">Player's name:</label>
				<div class="col-sm-10">
				  <input class="form-control" name="name" placeholder="Pseudo">
				</div>
			  </div>
			</form>
			<?
			
			$req=$_GET['name'];
			if($req) {
				$players=loadPlayer($req);
				
				foreach ($players as $player) {
					$pseudo=$player->{'pseudo'};
					$rank=$player->{'rank'};
					$userId=$player->{'codingamer'}->{'userId'};
					?><a href="?pid=<?= $userId ?>" class="btn btn-primary btn-lg"> <span class="badge">#<?= $rank ?></span> <?= $pseudo ?></a><?
				}
			}
			
		} else {
		
		?>	
	    <!--   CHOIX DE LA DIFFICULTE   -->
		
		<div id="levels" class="btn-toolbar" role="toolbar">
		  <div class="btn-group" role="group">
			<a id="easy"   type="button" class="btn btn-default">Easy</a>
			<a id="medium" type="button" class="btn btn-default">Medium</a>
			<a id="hard"   type="button" class="btn btn-default">Hard</a>
			<a id="expert" type="button" class="btn btn-default">Expert</a>
		  </div>
		  <div class="btn-group" role="group">
			<a id="optim"    type="button" class="btn btn-default">Optimisation</a>
			<a id="codegolf" type="button" class="btn btn-default">Code golf</a>
		  </div>
		  <!-- Moyen interressant, car un seul langage...
		  <div class="btn-group" role="group">
			<a id="machine-learning" type="button" class="btn btn-default">Machine learning</a>
		  </div>
		  -->
		  <!-- PAS de multi : c'est le but des outils de Magus
		  <div class="btn-group" role="group">
			<a id="multi"  type="button" class="btn btn-default">Multiplayer</a>
		  </div>
		  -->
		  <div class="btn-group" role="group">
			<a id="community" type="button" class="btn btn-default">Community</a>
		  </div>
 
<?


	$LEVEL=$_GET["level"]; # Le niveau easy/hard/.../multi...

	if ($LEVEL) {

		$PAGE=$_GET["p"];  # La pagination des langages
		if (!$PAGE) {$PAGE=1;}
		$PAGENUM = 2;

		if ($LEVEL==="machine-learning") {
				$PAGE=1;
			} else {


				?>
						<ul class="pagination pull-right">
						  <li id="1"<? if ($PAGE=="1" ) {?> class="active"<?}?>><a href="#">1</a></li>
						  <li id="2"<? if ($PAGE=="2" ) {?> class="active"<?}?>><a href="#">2</a></li>
						</ul>
				<?php
			}


		# Outil demandé par Asmodeus (et moi)

		# Pour un puzzle donné, il faut faire la requete :
		# https://www.codingame.com/services/PuzzleRemoteService/findAvailableProgrammingLanguages
		# En POST et avec en parametre [39, 802230]
		# 39 est l'ID du puzzle et 802230 l'ID du joueur

		require_once "lib/cache.class.php";

		# Voici la reponse à findGamesPuzzleProgress qui n'est pas utilisable ici (car il faut être connecté)
		# mais qui contient les infos sur tous les puzzles
		$string = file_get_contents("const/findGamesPuzzleProgress.json");
		$PUZZLES= json_decode($string)->{"success"};


		$cache = new Cache("player-$joueurID");
		$cache->setCachePath('cache/');
		$cache->eraseExpired();

		foreach ($PUZZLES as $puzzle) {	
			if ($puzzle->{"level"}==$LEVEL) { # FILTRE : on affiche uniquement le niveau demandé
				$ID=$puzzle->{"id"};
				$TITLE=$puzzle->{"title"};
				
				# Chargement des résultats du joueur
				$result = loadPuzzle($cache,$joueurID,$ID);
				
				# SI PREMIERE LIGNE : On calcule les colonnes (langages)
				if (! $column_count) {
					?><table class="table table-striped table-header-rotated"><thead><tr><th></th><?
					$cols=array();
					$column_count=0;
					foreach (pagination_slice($result,$PAGE,$PAGENUM) as $lang) {
						array_push($cols, $lang->{"id"});
						?><th class="rotate-45"><div><span><?= $lang->{"id"} ?></span></div></th><?
						$column_count++;
					}
					?></tr></thead><tbody><?
				}
				
				# AFFICHAGE DE LA LIGNE : Nom du puzzle
				?><tr><th class="row-header"><?= $TITLE ?></th><?
				# Valeurs pour chaque colonne
				for ($col=0;$col<$column_count;$col++) { #pagination_slice($result,$PAGE,$PAGENUM) as $line
					$c=0;
					while($c<count($result) && $result[$c]->{"id"}!=$cols[$col]) { $c++; }
					if($c>=count($result)) {
						?><td class="danger"></td><?
					} else {
						?><td><? if ($result[$c]->{"solved"}) { ?><span class="glyphicon glyphicon-ok green"/><? } else { ?>-<? } ?></td><?
					}
				}
				
				?></tr><?
			} // if puzzle==LEVEL
		} // foreach PUZZLES

	} // if LEVEL

} // if PLAYER ID
?></tbody></table>
	
	</div>
</div>

<script>
	// ------------------------------------------------
	//
	// Fonctions JS
	//
	
	function setLevel(level) {
		var newUrl = $.query.set("level", level)
		location.href=newUrl;
		
	}
	function setPage(page) {
		var newUrl = $.query.set("p", page)
		location.href=newUrl;
		
	}
	
	// ------------------------------------------------
	// INIT JQUERY
	//
	$('#levels a').on('click', function(event) {
		event.preventDefault();
		var level = event.target.id;
		setLevel(level);
		});
		
	if ($.query.get("level")) {
		$('#levels #'+$.query.get("level")).addClass('active');
	}

	$('.pagination li a').on('click', function(event) {
		event.preventDefault();
		var page = event.target.parentElement.id;
		setPage(page);
		});
</script>
<?

###################################################################################################
#
#    Fonctions PHP
#

	#
	# Extrait une portion de tableau en fonction de la page en cours
	#
	function pagination_slice($array,$PAGE,$NUMPAGE) {
		$PERPAGE = slice_count($array,$NUMPAGE);
		return array_slice($array,($PAGE-1)*$PERPAGE,$PERPAGE);
	}
	#
	# Le nombre de colonne dans une page
	#
	function slice_count($array,$NUMPAGE) {
		return ceil(count($array)/$NUMPAGE);
	}

	#
	# CHARGE le puzzle ID:$ID
	#
	function loadPuzzle( $cache, $joueurID, $ID ) {
		$API='https://www.codingame.com/services/PuzzleRemoteService/findAvailableProgrammingLanguages';
		$EXPIRATION = 60 * 60;
		$KEY="findGamesPuzzleProgress-$ID";
		$dec = $cache->retrieve($KEY);

		if (! $dec) {
			# <h1>LOADING (TODO)</h1>
			$data = "[$ID, $joueurID]";

			// use key 'http' even if you send the request to https://...
			$options = array(
				'http' => array(
					'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
					'method'  => 'POST',
					'content' => $data
				)
			);
			$context  = stream_context_create($options);
			$result = file_get_contents($API, false, $context);
			if ($result === FALSE) { ?> <h1>CG API Error</h1> <? }
			# var_dump( $result );
			$dec = json_decode($result);
			$cache->store($KEY,$dec,$EXPIRATION);
		}
		return $dec->{'success'};
	}
	
	#
	# CHARGE le joueur avec son pseudo : renvoie un tableau
	#
	function loadPlayer( $pseudo ) {
		$API='https://www.codingame.com/services/LeaderboardsRemoteService/getGlobalLeaderboard';
		$data = "[1,{\"keyword\":\"$pseudo\"},\"\",true,\"global\"]";

		// use key 'http' even if you send the request to https://...
		$options = array(
			'http' => array(
				'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				'method'  => 'POST',
				'content' => $data
			)
		);
		$context  = stream_context_create($options);
		$result = file_get_contents($API, false, $context);
		if ($result === FALSE) { ?> <h1>CG API Error</h1> <? }
		$dec = json_decode($result);
		return $dec->{'success'}->{'users'};
	}
	