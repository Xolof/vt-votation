<?php
if (!defined('ABSPATH')) {
  exit;  // Exit if accessed directly.
}
?>

<h1><?= __('Manual', 'my-textdomain'); ?></h1>
<h3>Syfte</h3>
<p>Tanken med pluginet är att skapa ett bra flöde för slutanvändarna, förhindra spam och sammanställa resultaten från omröstningen.</p>
<h3>Funktionalitet</h3>
<ul>
  <li>Möjliggör koppling mellan knappar på omröstningssidan med deras motsvarande checkboxes.</li>
  <li>Sammanställer resultat från omröstning och visar upp i admin.</li>
  <li>Tar endast emot en röst per epostadress.</li>
  <li>Loggar IP-adresser från inlämingar.</li>
  <li>Gör det möjligt att endast tillåta en inlämning per IP-adress.</li>
</ul>
<h3>Skapa en omröstning:</h3>
<ol>
  <li>Installera pluginet Forminator om det inte redan är installerat.</li>
  <li>Skapa ett formulär i Forminator.</li>
  <li>Skapa ett fält med checkboxes.</li>
  <li>
    Lägg till ett alternativ för varje bok.
    Fältet "value" kommer att bli en slug, till exempel Min-olämpliga-bok.
  </li>
  <li>Spara formuläret.</li>
  <li>Skapa en sida i Wordpress.</li>
  <li>Lägg till formuläret du nyss skapade på sidan.</li>
  <li>Lägg in text och bilder om varje bok.</li>
  <li>Lägg in en knapp efter varje boks stycke.</li>
  <li>Knappens text ska vara "Lägg till i min lista".</li>
  <li>Längst ner under knappens inställningar finns ett fält som heter "Additional CSS class(es). Lägg till följande textsträng där utan kommatecken: "votationButton Min-olämpliga-bok". Ersätt Min-olämpliga-bok med värdet från fältet "value" från motsvarande checkbox i Forminator.</li>
  <li>Spara sidan.</li>
  <li>I admin-menyn i Wordpress gå till "Årets olämpligaste barnbok" >> "Inställningar".</li>
  <li>I dropdown-menyerna, välj den sida och det formulär du nyss skapade. Klicka "Spara".</li>
  <li>Gå till sidan i din webbläsare. Nu ska knapparna vara kopplade till respektive checkbox. Knappar och checkboxes ska synkas med varandra när man klickar på dem. Resultat ska visas i Wordpress admin under "Årets olämpligaste barnbok" >> Resultat.</li>
  <li>Om det inte fungerar, kontrollera att du har följt alla stegen. Om det fortfarande inte fungerar, skicka ett mejl till contact@liberdev.se så felsöker vi tillsammans ;)</li>
</ol>
