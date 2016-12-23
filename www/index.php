<?
require "lib/cache.class.php";

require 'lib/utils.php';
require 'lib/players.php';
require 'lib/puzzles.php';

$EXPIRATION_H = 1 ; // 1 heure d'expiration des données
$COLORS=array( "green", "red", "orange", "purple", "cyan", "white" );

// *****************************
//   VERSION MULTI JOUEUR (permet d'utiliser la syntaxe pid=[1,2,3] dans l'url )
// Info : pour debugger 
//	error_log("test");
// apparait dans                         docker logs -f cgpuzzle | grep error
// *****************************


include 'inc/header.php';

?><script>
	// ------------------------------------------------
	//
	// Fonctions JS (appelées par les events JQuery)
	//
	
	function setLevel(level) {
		var newUrl = $.query.set("level", level);
		location.href=newUrl;
		
	}
	function setAdd() {
		var newUrl = $.query.set("add", 1);
		location.href=newUrl;
		
	}
	function setSearch() {
		var newUrl = $.query
			.set("search", $('input:first').val());
		location.href=newUrl;
		
	}
	function setPage(page) {
		var newUrl = $.query.set("p", page);
		location.href=newUrl;
	}
	function appendPlayer(pid) {
		var oldPid = $.query.get("pid");
		var newPid = ($.query.get("pid")+","+pid)
			.replace(/^,/,'');
		var newUrl = $.query
			.set("pid", newPid )
			.remove("search")
			.remove("add");
		location.href=newUrl;		
	}
	function removePlayer(pid) {
		var oldPid = $.query.get("pid");
		var re = ',?'+pid+',?';
		var newPid = oldPid.replace(new RegExp(re,'g'),",,")
			.replace(/,+/g,',').replace(/^,|,$/g,'');
		var newUrl = $.query
			.set("pid",newPid);
		location.href=newUrl;		
	}
	</script><?
	
/*?><h3>DEBUG EN COURS</h3><?*/

$joueurID=$_GET["pid"];
$search=$_GET['search'];
$add=$_GET['add'];
$LEVEL=$_GET["level"]; # Le niveau easy/hard/.../multi...


// *********************************************************************************************************
// 
// AFFICHAGE DE LA RECHERCHE PAR PSEUDO
// 
// *********************************************************************************************************

if (!$joueurID || $add) { // Aucun utilisateur (pid) sélectionné : on propose un champ de recherche de pseudo

	include 'inc/searchBar.php';
	
	if($search) { // On a tapé un nom, alors on affiche tous les joueurs correspondant
		$players=searchPlayerByPseudo($search);
		if (count($players)==1) {
			$userId=$players[0]->{'codingamer'}->{'userId'};
			$pseudo=$players[0]->{'pseudo'};
			?> <div><h4>Found '<?= $pseudo ?>' ...</h4></div> <?
			?><script> appendPlayer(<?= $userId ?>) </script><?
		} else {
			?> <div><h4>Players found for '<?= $search ?>' :</h4></div> <?
			$listPlayersHtml = getPlayerBlockOutput($players);
			echo implode($listPlayersHtml);
		}
	}
}

if ($joueurID) {

// *********************************************************************************************************
// 
// AFFICHAGE DU OU DES PSEUDOS DES JOUEURS
// 
// *********************************************************************************************************

	// $joueursID = serialize(  array() );
	$joueursID = explode(',',$joueurID);
	
	//var_dump_pre($joueursID);
	// TODO : DEV EN COURS : la partie génération de l'url (choix de plusieurs joueurs) et analyse de cette URL n'existe pas encore
	// Pour le moment, je mets des PID en dur
	
	//$joueursID = array(802230,1713461,1417888,804332,1498867);
		//var_dump_pre($joueursID);
	// **************************************************************
	// 
	// AFFICHAGE DES LEGENDES DES JOUEURS
	// 
	// **************************************************************
	?><div id="legends"><?
	echo implode( getPlayerLegendOutput( $joueursID, $COLORS ));
	if (! $add && count($joueursID)<6) {
		include 'inc/addButton.inc';	
	}
		?></div><?
	
	if ($add) {
		// Pas d'affichage du reste, car on est dans une procédure d'ajout d'utilisateur
	} else {
	
		// **************************************************************
		// 
		// AFFICHAGE DES ONGLETS "Easy, Medium..."
		// 
		// **************************************************************
		include	'inc/levels.inc';
		
		foreach ($joueursID as $joueurID) {
			# Création du fichier de cache du joueur
			$caches[$joueurID] = createPlayerCacheFile($joueurID);
		}

		// **************************************************************
		// 
		// AFFICHAGE DE LA TABLE PRINCIPALE
		// 
		// **************************************************************
		
		
		displayPuzzleTable( $caches, $LEVEL, $joueursID, $COLORS );

	}

} // if PLAYER ID
?></tbody></table>
	
	</div>
</div>

<script>

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
		
	$('#add-player').on('click',  function(event) {
		setAdd();
		});
		
	$('#search').submit(function(event) {
		event.preventDefault();
		setSearch();
		});
		
	$('.search-player-result').on('click', function(event) {
		event.preventDefault();
		var pid = $(event.target).attr('pid');
		appendPlayer(pid);
		});
		
	$('.remove-player').on('click', function(event) {
		event.preventDefault();
		var pid = $(this).attr('pid');
		removePlayer(pid);
		});
		
	$('[data-toggle="tooltip"]').tooltip();
</script>

	
