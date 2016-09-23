
<?php

# Outil demandé par Asmodeus (et moi)

# Pour un puzzle donné, il faut faire la requete :
# https://www.codingame.com/services/PuzzleRemoteService/findAvailableProgrammingLanguages
# En POST et avec en parametre [39, 802230]
# 39 est l'ID du puzzle et 802230 l'ID du joueur

$API='https://www.codingame.com/services/PuzzleRemoteService/findAvailableProgrammingLanguages';

$puzzleID=40;
$joueurID=802230;

$data = "[$puzzleID, $joueurID]";

// use key 'http' even if you send the request to https://...
$options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        # 'content' => http_build_query($data)
        'content' => $data
    )
);
$context  = stream_context_create($options);
$result = file_get_contents($API, false, $context);
if ($result === FALSE) { /* Handle error */ }

 var_dump( $result );
$dec = json_decode($result);
?>
		<table border="1">
		<header><th>Lang</th><th>Solved</th><th>Last</th><th>Onboarding</th></header>
<?
foreach ($dec->{'success'} as $line) {
?>
		<tr>
		<th><?= $line->{"id"} ?></th>
		<td><? if ($line->{"solved"}) { ?>Oui<? } else { ?>Non<? } ?></td>
		<td><?= $line->{"last"} ?></td>
		<td><? if ($line->{"onboarding"}) { ?>Oui<? } else { ?>Non<? } ?></td>
		</tr>
<?
}
?>
		</table>
