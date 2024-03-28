<?php
// Random Generierung der Minen in vorgegebenem Raster. 
// Danach bei öffnen eines Feldes schauen, wieviele Minen drumherum sind. 
// Eintrag der Nummer auf dem Feld. Falls Feld keine Minen um sich hat, öffenen aller Felder drumherum (cascadierend).
// 

// Hier Konfigurationsvariablen!
$dateiName = "minesweeper.txt";

// Ist die Variable "flag" gesetzt?
if (isset($_POST['flag'])) {
    if ($_POST['flag']=='true') $flag=true;
    else $flag=false;
}
else $flag=false;

// Soll ein Feld geöffnet werden?
$feld_oeffnen=false;
if (isset($_POST['feld'])) { 
    $feld=(int)$_POST['feld']; 
    $feld_oeffnen=true; 
}
else $feld = -1;

// Ab hier Funktionen!

// Ein neues Spielfeld wird abgefragt!
function neuesSpielfeld() {     
    echo '<html>
    <form action="minesweeper.php" id="Spielfeld" method="post">
    <label class="h2" form="Spielfeld">Wie groß soll das Spielfeld werden?</label><br>
    <label for="spalten">Spalten</label> 
    <input type="int" name="spalten" id="spalten" maxlength="2"><br>
 
    <label for="zeilen">Zeilen</label>  
    <input type="int" name="zeilen" id="zeilen" maxlength="2"><br>

    <label for="sbomben">Wieviele Bomben sollen im Spiel sein?</label> 
    <input type="int" name="bomben" id="bomben" maxlength="2"><br>

    <button type="submit">Eingaben absenden</button>
</form><br>
<form action="minesweeper.php" id="Spielfeld" method="post">
    <input type="hidden" name="zeilen" value="10">
    <input type="hidden" name="spalten" value="10">
    <input type="hidden" name="bomben" value="10">
    <button type="submit">Einfach</button>
</form>
<form action="minesweeper.php" id="Spielfeld" method="post">
    <input type="hidden" name="zeilen" value="15">
    <input type="hidden" name="spalten" value="15">
    <input type="hidden" name="bomben" value="50">
    <button type="submit">Mittel</button>
</form>
<form action="minesweeper.php" id="Spielfeld" method="post">
    <input type="hidden" name="zeilen" value="20">
    <input type="hidden" name="spalten" value="20">
    <input type="hidden" name="bomben" value="150">
    <button type="submit">Schwer</button>
</form>

</html>';
}

// Nach neuesSpielfeld() kommt diese Funktion und erstellt ein neues Spielfeld mit den gegebenen Parametern!
function spielfeldErstellen($S, $Z, $B, $dateiName){        
    $zellen = $S*$Z;
    $Bombe[0] = 0;
    for ($i=0; $i < $zellen; $i++) { 
        $db_info[$i] = 0;
    }
    $Felderoffen = implode(";", $db_info);
    for ($i=0; $i < $B; $i++) {
        $Bombe[$i] = rand(1,$zellen);
        $test = false; $test2 = false;
        if ($i > 0) {
            while ($test2 != true){
                for ($x=0; $x < $i; $x++) {
                    if ($Bombe[$i] == $Bombe[$x]) {
                        $test = true;
                    }
                    else $test2 = true;
                }
                if ($test == true) {
                    $Bombe[$i] = rand(1,$zellen);
                    $test = false;
                    $test2 = false;
                }                
            }
        }
        $var = $Bombe[$i]-1;
        $db_info[$var] = 1;
    }
    spielfeldSpeichern($S, $Z, $B, implode(";", $db_info), $Felderoffen, $dateiName);
}

// $Spalten, $Zeilen, $Bomben, $Felder_mit_Bomben, $Felder_offen/flagged
function spielfeldSpeichern($S, $Z, $B, $db_info, $Felderoffen, $dateiName) {   
    $eintrag = $S.";".$Z.";".$B.";".$db_info.";".$Felderoffen;
    file_put_contents($dateiName, $eintrag);
}

// Spiel läuft, Ausgabe Spielfeld und Abfrage Eingabe
function displayGame($db_info,$flag,$feld,$dateiName) {            
    echo '<form id="Spielfeld" method="post">
            <input type="radio" name="flag" value=true'; if ($flag!=false) echo " checked"; echo '><label for="flag">Flagge setzen</label>';
    echo '  <input type="radio" name="flag" value=false';if ($flag==false) echo " checked"; echo '><label for="flag">Feld öffnen</label>
            <table>
                <tr>';
    $Felder = $db_info[0]*$db_info[1];
    $Spalte = $db_info[0];
    for ($i=0; $i<$Felder;$i++) {
        
        // Wenn neue Spalte mache neuen Tabellenabschnitt
        if ($i == $Spalte) { echo "</tr><tr>"; $Spalte = $Spalte+$db_info[0]; }       

        // Ist eine Flagge gesetzt? Wenn ja dann ->
        if (fieldFlagged($dateiName,$i)==true) {
            echo '<td><button type="submit" class="button" name="feld" value="'.$i.'"><img class="img" src="Minesweeper/flagge.gif"></img></button></td>'; 
        }
        else // Sonst, wenn keine Flagge gesetzt ist 
            // if Bedingung ob Feld aufgedeckt oder nicht. Wenn ja Bomben drumherum berechnen und ausgeben sonst Button!
            if (fieldOpened($dateiName,$i)==true) { // Feld geöffnet? Wenn ja dann ->
                echo '<td><img class="img" src="Minesweeper/'.fieldBombs($dateiName, $i).'.gif"></img></td>';
            }

        // -> sonst wenn Feld nicht geöffnet! -> Mach geschlossenes Feld mit Button
        else echo '<td><button type="submit" class="button" name="feld" value="'.$i.'"><img class="img" src="Minesweeper/verdeckt.gif"></img></button></td>';
    }                
                       
                    echo '</tr>
                </table>
            </form>';
}

// Flagge setzen. Benötigt $dateiName und das Feld, welches geflaggt wird.
function setFlag($dateiName, $feld_nr) {                
    $db_info = dateiLesen($dateiName);
    ($db_info[4][$feld_nr]==0) ? $db_info[4][$feld_nr]=2 : $db_info[4][$feld_nr]=0;
    $bomben = implode(";", $db_info[3]);
    $felderoffen = implode(";", $db_info[4]);
    spielfeldSpeichern($db_info[0], $db_info[1], $db_info[2], $bomben, $felderoffen, $dateiName);
}

// checkt, ob das Feld geflaggt ist und gibt true oder false zurück
function fieldFlagged($dateiName, $feld_nr) {           
    $db_info = dateiLesen($dateiName);
    if ($db_info[4][$feld_nr]=='2') return true; else return false;
}

// checkt, ob das Feld schon geöffnet wurde und gibt true oder false zurück
function fieldOpened($dateiName, $feld_nr) {            
    $db_info = dateiLesen($dateiName);
    if ($feld_nr != -1) { 
        if ($db_info[4][$feld_nr]!='0') return true; else return false;
    }
}

// gibt die Anzahl Bomben zurück, die um das Feld liegen
function fieldBombs($dateiName, $feld_nr) {             
    $db_info = dateiLesen($dateiName);

    // Array mit Bomben wird erzeugt
    $a=0;
    for ($i=0; $i<$db_info[1]; $i++) {                                          
        for ($x=0; $x<$db_info[0]; $x++) {
            $BOMBEN[$x][$i]=$db_info[3][$a]; $a++;
        }
    }
        
    // Koordinaten Y werden ermittelt
    if ($feld_nr <= ($db_info[0]*$db_info[1])) {
        $y = (int)(($feld_nr) / $db_info[0]);
    }

    // var_dump($BOMBEN);
    // echo "Feld= " .$feld_nr;
    // echo "<br>A= ".$a;
    // echo "<br>Y= ".$y;

    // Koordinaten X werden ermittelt
    $x=$feld_nr;                                                            
    while ($x >= $db_info[0]) $x -= $db_info[0];
    // echo "<br>X= ".$x ." Y= ". $y . "<br>";
    // Anzahl umliegender Bomben wird ermittelt
    $Bomben = 0; 
    if (isset($BOMBEN[$x-1][$y])) $Bomben += $BOMBEN[$x-1][$y];     // Links        
    if (isset($BOMBEN[$x+1][$y])) $Bomben += $BOMBEN[$x+1][$y];     // Rechts
     
    if (isset($BOMBEN[$x-1][$y-1])) $Bomben += $BOMBEN[$x-1][$y-1]; // Oben Links
    if (isset($BOMBEN[$x][$y-1])) $Bomben += $BOMBEN[$x][$y-1];     // Oben
    if (isset($BOMBEN[$x+1][$y-1])) $Bomben += $BOMBEN[$x+1][$y-1]; // Oben Rechts

    if (isset($BOMBEN[$x-1][$y+1])) $Bomben += $BOMBEN[$x-1][$y+1]; // Unten Links
    if (isset($BOMBEN[$x][$y+1])) $Bomben += $BOMBEN[$x][$y+1];     // Unten 
    if (isset($BOMBEN[$x+1][$y+1])) $Bomben += $BOMBEN[$x+1][$y+1]; // Unten Rechts

    // Anzahl umliegender Bomben wird zurück gegeben
    // echo "Bomben= ".$Bomben."<br><br>";
    return $Bomben;     
}

// öffnet die Felder, die um das Feld liegen
function openRoundFields($dateiName, $feld_nr) {             
    $db_info = dateiLesen($dateiName); $wert=0;
    $spalten = $db_info[0];
    $zeilen = $db_info[1];

    // Array mit Feldern wird erzeugt
    for ($i=0; $i<$zeilen; $i++) {                                          
        for ($x=0; $x<$spalten; $x++) {
            $FELD[$x][$i]=$wert; $wert++;
        }
    }

    // Koordinaten Y werden ermittelt
    if ($feld_nr <= ($db_info[0]*$db_info[1])){
        $y = (int)(($feld_nr) / $db_info[0]);
    }

    // Koordinaten X werden ermittelt
    $x=$feld_nr;                                                            
    while ($x >= $spalten) $x -= $spalten;                                  

    // Umliegende Felder werden geöffnet
    
    isset($FELD[$x-1][$y]) AND (fieldOpened($dateiName, $FELD[$x-1][$y])!=true) ? openField($dateiName,$FELD[$x-1][$y]) : print "";         // Links
    isset($FELD[$x+1][$y]) AND (fieldOpened($dateiName, $FELD[$x+1][$y])!=true) ? openField($dateiName,$FELD[$x+1][$y]) : print "";         // Rechts

    isset($FELD[$x-1][$y-1]) AND (fieldOpened($dateiName, $FELD[$x-1][$y-1])!=true) ? openField($dateiName,$FELD[$x-1][$y-1]) : print "";   // Oben Links
    isset($FELD[$x][$y-1]) AND (fieldOpened($dateiName, $FELD[$x][$y-1])!=true) ? openField($dateiName,$FELD[$x][$y-1]) : print "";         // Oben
    isset($FELD[$x+1][$y-1]) AND (fieldOpened($dateiName, $FELD[$x+1][$y-1])!=true) ? openField($dateiName,$FELD[$x+1][$y-1]) : print "";   // Oben Rechts
    
    isset($FELD[$x-1][$y+1]) AND (fieldOpened($dateiName, $FELD[$x-1][$y+1])!=true) ? openField($dateiName,$FELD[$x-1][$y+1]) : print "";   // Unten Links
    isset($FELD[$x][$y+1]) AND (fieldOpened($dateiName, $FELD[$x][$y+1])!=true) ? openField($dateiName,$FELD[$x][$y+1]) : print "";         // Unten
    isset($FELD[$x+1][$y+1]) AND (fieldOpened($dateiName, $FELD[$x+1][$y+1])!=true) ? openField($dateiName,$FELD[$x+1][$y+1]) : print "";   // Unten Rechts
}

// Feld aufdecken. Benötigt $dateiName und das Feld, welches geöffnet wird. 
function openField($dateiName, $feld_nr) {        
    $db_info = dateiLesen($dateiName);
    if ($db_info[4][$feld_nr]=='2') { 
        // echo "Hier steht ne Flagge!<br>"; 
        // var_dump($db_info);
    } 
    else if ($db_info[3][$feld_nr]!='1') {  // Schaut, ob auf dem geöffneten Feld eine Bombe liegt. Wenn nein dann ->
        $db_info[4][$feld_nr]=1;    // Feld als geöffnet markieren
        
        // Daten zusammenfassen und Spielfeld speichern
        $bomben = implode(";", $db_info[3]);
        $felderoffen = implode(";", $db_info[4]);
        spielfeldSpeichern($db_info[0], $db_info[1], $db_info[2], $bomben, $felderoffen, $dateiName);

        // Schauen, ob das geöffnete Feld Bomben um sich rum hat. Wenn ja openRoundFields()
        (fieldBombs($dateiName, $feld_nr) == 0) ? openRoundFields($dateiName, $feld_nr) : print "";
    }
        else {  // Sonst, wenn auf dem Feld eine Bombe liegt ->
            echo '<img src="Minesweeper/explosion.gif"></img><br>';
            echo "BOOOM! Du hast verloren!<br>";
            deleteGame($dateiName);
        }

}   

// Funktion zur überprüfung ob alle möglichen Felder geöffnet wurden
function getOpenFields($dateiName) {
    $db_info = dateiLesen($dateiName);    // returns array($db_info[0],$db_info[1],$db_info[2],$FeldMinen,$FeldOffen)
    $a = 0;
    foreach ($db_info[4] as $value) {
        ($value == 1) ? $a++ : print "";
    }
    return $a;
}

// Aktuelles Spiel wird gelöscht
function deleteGame($dateiName) {             
    if (unlink($dateiName)) {
        echo "Spiel beendet!<br>";
        die();
    }
    else echo "Da ist etwas schief gegangen!";
}

// Liest die Datei $dateiName und gibt ein Array zurück mit 0=Spalten, 1=Zeilen, 2=Bomben, 3=Array[gesetzte Bomben], 4=Array[geöffnete Felder/Flaggen]
function dateiLesen($dateiName) {   
    $db = file($dateiName);
    foreach($db AS $line) { 
        $db_info = explode(";", $line);
    }
    $Felder=$db_info[0]*$db_info[1]; $x=0;
    for ($i=3; $i<($Felder+3);$i++){
        $FeldMinen[$x]=(int)$db_info[$i]; $x++;
    }
    $x=0;
    for (($i=3+$Felder); $i<(($Felder*2)+3);$i++){
        $FeldOffen[$x]=$db_info[$i]; $x++;
    }
    return array($db_info[0],$db_info[1],$db_info[2],$FeldMinen,$FeldOffen);
}

// HTML Header
function htmlHead() {               
    echo '
    <head>
    <style>
        .flex-container {
            display: flex;
            background-color: white;
        }

        .flex-item {
            background-color: white;
            width: 20px;
            margin: 1px;
            text-align: center;
            line-height: 20px;
            font-size: 5px;
        }
        
        .button-group {
            display: flex;
         }

        .button {
            background-color: gray; 
            border: 1px;
            color: white;
            padding: 0px 0px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 0px;
        }
        .img {
            widht: 25px;
            height: 25px;
        }
    </style>
</head>';
}

// Begin eigentliches Programm
echo '<html>';
htmlHead();                         // Hier steht der HTML Kopf
echo '<body>';                      // Ab hier Code



if ($flag==true) setFlag($dateiName, $feld); 
if ($flag==false AND $feld!=-1) openField($dateiName, $feld);
if (isset($_POST['spielende'])) if ($_POST['spielende']==true) deleteGame($dateiName);

if (file_exists($dateiName)) {          // Sieht nach, ob Spiel existiert. Wenn ja ->
    $db_info = dateiLesen($dateiName);
    $felderoffen = ($db_info[0]*$db_info[1])-$db_info[2];
    if (getOpenFields($dateiName)==$felderoffen) echo "<br>Herzlichen Glückwunsch!<br><br><br>";          // Sieht nach, ob Spielfeld gelöst wurde
    displayGame($db_info,$flag,$feld,$dateiName);
}
else {                              // Sollte Spiel nicht existieren, dann ->
    if (isset($_POST['spalten']) AND isset($_POST['zeilen']) AND isset($_POST['bomben'])){
        if (($_POST['spalten'] != '') AND ($_POST['zeilen'] != '') AND ($_POST['bomben'] != '')){
            spielfeldErstellen($_POST['spalten'],$_POST['zeilen'],$_POST['bomben'],$dateiName);
            $db_info = dateiLesen($dateiName);
            displayGame($db_info,$flag,$feld,$dateiName);
        }
        else neuesSpielfeld();
    }
        else neuesSpielfeld();
}
if (file_exists($dateiName) != false) {
    echo '<form id="spielende" method="post"><button type="submit" name="spielende" value="true">Spiel beenden</button></form>';
}
echo '</body>';
?>