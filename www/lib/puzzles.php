<?php

//**********************************************************************
//
// Fonctions de manipulation des puzzles
//
// displayPuzzleTable( $caches, $LVL, $jIds, $COLS ) : Affiche la table
//
//**********************************************************************

function loadPuzzles() {
	# Si le navigateur de l'utilisateur n'est pas en fran�ais, alors on lit le fichier anglais
	$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
	if ($lang!='fr') {
		$lang='en';
	}
	# TODO : Tenter un chargement dynamique avec connexion priv�e
	# Ce fichier a �t� t�l�charg� � la main (avec ma connexion) et d�pos� sur le serveur
	$string = file_get_contents("const/findGamesPuzzleProgress.json.$lang");
	if ($string) {
		return json_decode($string)->{"success"};
	} else {
		?><h1>Fichier 'puzzles' manquant</h1><?
	}
}

# Avec le tableau des achievements du joueur, on d�termine si le puzzle pass� en param�tre
# poss�de au moins un achievement associ�
# Renvoie le taux de compl�tion du puzzle (entre 0 et 100) ou false s'il n'existe pas d'achievement pour ce puzzle
function getPuzzleSolved($achievements, $puzzle) { 
	$puzzleId = $puzzle->{"id"};
	# Erreur de CG !
	if ($puzzleId==55) {$puzzleId=53;}
	if ($puzzleId==54) {$puzzleId=52;}
	if ($puzzleId==121) {$puzzleId=3;}
	# On cherche les "id" de la forme "PZ_ddP_P123"
	$max=0;
	$exist=false;
	foreach($achievements as $achievement) {
		$id=$achievement->{'id'};
		$progress=$achievement->{'progress'};
		if (preg_match( "/PZ_(\d+)P_P$puzzleId$/", $id, $groups)) {
			$exist=true;
			if ($progress>0){
				$per=$groups[1];
				if ($per>$max) {$max=$per;}
			}
		}
	}
	return $exist ? $max : false;
}

# Permet de savoir si un puzzle a �t� r�solu par le joueur. Comme ce test est bas� sur les achievements,
# dans le cas o� le puzzle n'a pas d'achievement associ�, alors on r�pond true, dans le doute.
function isPuzzleSolved($achievements, $puzzle) { 
	$percent = getPuzzleSolved($achievements, $puzzle);
	if ($percent===false) { return true; }
	return $percent>0;
}

#
# CHARGE le puzzle ID:$ID
#
# renvoie un tableau de { ["id"]=>"Bash"    ["solved"]=>false    ["last"]=>false    ["onboarding"]=>false  }
#
function loadPuzzle( $cache, $joueurID, $ID ) {
	global $EXPIRATION_H;
	$API='https://www.codingame.com/services/PuzzleRemoteService/findAvailableProgrammingLanguages';
	$EXPIRATION = 1+$EXPIRATION_H * 60 * 60;
	$KEY="findGamesPuzzleProgress-$ID";
	$dec = $cache->retrieve($KEY); // Le fichier de cache contient toutes les infos li�es � un joueur, et la cl� indique le r�sultat pour le puzzle en question.

	if (! $dec) {
		// var_dump_pre("load Puzzle $joueurID, $ID");
		# <h1>LOADING (TODO)</h1>
		$result = POST($API, "[$ID, $joueurID]");
		if ($result === FALSE) { ?> <h1>CG API Error</h1> <? }
		# var_dump( $result );
		$dec = json_decode($result);
		$cache->store($KEY,$dec,$EXPIRATION);
	}
	
	// un Array d'objet pour chacun des 25 langages : 
	//  { ["id"]=>"Bash"    ["solved"]=>false    ["last"]=>false    ["onboarding"]=>false  }
	//error_log("\nPUZZLE=".var_dump_ret($dec->{'success'}));
	
	return $dec->{'success'};
}

function computeUsedLangages($caches, $achievements, $PUZZLES, $LEVEL, $joueursID) {
	$langUsed = array();
	foreach ($PUZZLES as $puzzle) {	
		if ($puzzle->{"level"}==$LEVEL) { # On cherche tous les puzzle de la cat�gorie
			$ID=$puzzle->{"id"};
			foreach ($joueursID as $joueurID) {
				# Optimisation : on v�rifie si le joueur a r�solu (m�me partiellement) ce puzzle, car
				# dans le cas contraire, c'est inutile d'appeler loadPuzzle pour rien
				 if ( isPuzzleSolved($achievements[$joueurID], $puzzle)) {
					$result = loadPuzzle($caches[$joueurID],$joueurID,$ID);
					foreach ($result as $lang) { 
						$langId = $lang->{"id"};
						if (! in_array($langId, $langUsed)) {
							if ($lang->{"solved"}) { # Et pour ceux qui ont �t� r�solus par au moins un joueur
								$langUsed[] = $langId;
							}
						}
					}
				 } 
			}
		}
	}
	sort($langUsed);
	return $langUsed;
}

#
# CHARGE tous les achievements d'un joueur
#

function loadAchievements( $cache, $joueurID ) {
	global $EXPIRATION_H;
	$EXPIRATION = $EXPIRATION_H * 60 * 60;
	$KEY="achievements";
	$dec = $cache->retrieve($KEY); 

	# Si la cl� existe dans le cache, on retourne la valeur
	if ($dec) {
		return $dec->{'success'};
	}
	# Sinon, on fait la requ�te sur l'API
	$API='https://www.codingame.com/services/AchievementRemoteService/findByCodingamerId';
	$result = POST($API, "[$joueurID]");
	if ($result === FALSE) { ?> <h1>CG API Error</h1> <? }
	else {
		$dec = json_decode($result);
		$cache->store($KEY,$dec,$EXPIRATION);
		return $dec->{'success'};
	}
}


function displayPuzzleTable( $caches, $LEVEL, $joueursID, $COLORS ) {
	if (!$LEVEL) { ?><br><br><br><br><h4>Choose a type of puzzle.</h4><? return;}
	
	# Pour un puzzle donn�, il faut faire la requete :
		# https://www.codingame.com/services/PuzzleRemoteService/findAvailableProgrammingLanguages
		# En POST et avec en parametre [39, 802230]
		# 39 est l'ID du puzzle et 802230 l'ID du joueur


		$PUZZLES=loadPuzzles();
		
		foreach ($joueursID as $joueurID) {
			$achievements[$joueurID] = loadAchievements($caches[$joueurID], $joueurID);
		}

		$langUsed = computeUsedLangages($caches, $achievements, $PUZZLES, $LEVEL, $joueursID);

		# La pagination des langages
		$PAGE=$_GET["p"]; if (!$PAGE) {$PAGE=1;}
		if (count($langUsed)<=19) {$PAGENUM=1;}else {$PAGENUM=2;}
		if ($PAGE>$PAGENUM) { $PAGE=$PAGENUM; }
		display_pager($PAGE,$PAGENUM);
		
		# Calcule les langes � afficher
		$cols = pagination_slice($langUsed,$PAGE,$PAGENUM);

		// --------------------- AFFICHE LA PREMIERE LIGNE (Langages) ---------------
		?><table class="main-table table table-striped table-header-rotated"><thead><tr><th></th><th></th><?
		
		$langCount=count($cols);
		if ($langCount>0) {
			foreach ($cols as $langId) {
				?><th class="rotate-45"><div><span><?= $langId ?></span></div></th><?
				$column_count++;
			}
		} else {
			?><th class="rotate-45"><div><span>(None solved)</span></div></th><?
		}
		?></tr></thead><tbody><?
					
		// --------------------- AFFICHE LES LIGNES SUIVANTES (Puzzles) ---------------					
		foreach ($PUZZLES as $puzzle) {	
			if ($puzzle->{"level"}==$LEVEL) { # FILTRE : on affiche uniquement le niveau demand�
				$ID=$puzzle->{"id"};
				$TITLE=$puzzle->{"title"};
				$URL="http://www.codingame.com" . $puzzle->{"detailsPageUrl"};
				
				$lastKnownResult =array();
				# Chargement des r�sultats des joueurs
				foreach ($joueursID as $jID) {
					if ( isPuzzleSolved($achievements[$jID], $puzzle)) {
						$puzzleResults[$jID] = loadPuzzle($caches[$jID],$jID,$ID);
						$lastKnownResult = $puzzleResults[$jID];
					}
				}
				
				# Contient un tableau dont les langages sont dans le m�me ordre que tous les $puzzleResults
				$langOrder =  array_map(function($e) {
						return is_object($e) ? $e->id : $e['id'];
					}, $lastKnownResult);
				
				# $puzzleResults = array [   { ["id"]=>"Bash"    ["solved"]=>false    ["last"]=>false    ["onboarding"]=>false  } .... ]
				
				# -------------------------   AFFICHAGE DE LA LIGNE DE PUZZLE
				?><tr><th class="percent" width="<?= 10 + 20 * (count($joueursID)) ?>px"><?
				
				# R�ussite de chaque joueur
				$i=0;
				foreach ($joueursID as $jID) {
					$name=getPlayerPseudo($jID);
					$color=$COLORS[$i];$i++;
					$percent = getPuzzleSolved($achievements[$jID], $puzzle);
					if ( $percent==100) {
						?><span class="glyphicon glyphicon-ok-sign <?=$color?>" data-toggle="tooltip" title="Solved 100% by <?= $name ?>"></span><?
					} else 
					if ( $percent>0) {
						?><span class="glyphicon glyphicon-adjust <?=$color?>" data-toggle="tooltip"  title="Solved <?= $percent ?>% by <?= $name ?>"></span><?
					} else
					if ( $percent===false ) {
						# Il n'y a pas d'achievement sur �a ou alors c'est une erreur
					} else {
						?><span class="glyphicon glyphicon-remove-circle <?=$color?>"></span><?
						}
				}
				
				# Nom du puzzle
				?></th><th class="row-header"><a href="<?=$URL?>" target="_new"><?= $TITLE ?></a></th><?
				
				# NOTE : OK, c'est pas terrible, mais ca marche. J'utilise $result[$jID] en dehors de toute boucle sur $jID
				# car tous les tableaux ont la m�me structure, et j'utilise donc l� le dernier jID rencontr�.
				
				# Valeurs pour chaque colonne
				foreach ($cols as $langId) { 
					# On recherche dans $langOrder l'indice $c qui correspond � la colonne $langId
					$c=array_search($langId, $langOrder);
					if($c === false && count($lastKnownResult)>0) {
						# le langage n'est pas repr�sent� pour ce puzzle
						?><td class="danger"></td><?
					} else {
						?><td><?
						$countResult=0;
						$i=0;
						foreach ($joueursID as $jID) {
							$color=$COLORS[$i];$i++;
							# Si le joueur a ce puzzle, on affiche son badge
							if ( isPuzzleSolved($achievements[$jID], $puzzle) && $puzzleResults[$jID][$c]->{"solved"}) {
								?><span class="glyphicon glyphicon-ok <?=$color?>"></span><? 
								$countResult++;
							} 
						}
						# Si aucun des joueurs n'a ce puzzle, on remplit la case
						if ($countResult==0) {
							?>-<?
						}
						
						?></td><?
					}
				}
				
				?></tr><?
			} // if puzzle==LEVEL
		} // foreach PUZZLES
}
