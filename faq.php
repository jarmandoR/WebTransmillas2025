<?php

$title          = "transmillas empresa de carga";

$templateFinal  = implode("", file("./templates/FAQ.html"));
$templateFinal  = str_replace("[TITULO]"           	, $title          , $templateFinal);

$menu_menu    = implode("", file("./templates/menu.html"));
$templateFinal  = str_replace("[MENU]"            	, $menu_menu      , $templateFinal);

$menu_menu    = implode("", file("./templates/footer.html"));
$templateFinal  = str_replace("[FOOTER]"            , $menu_menu      , $templateFinal);

echo $templateFinal;

?>


