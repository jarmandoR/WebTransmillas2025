
<script>


var URLactual = window.location;
var urlno = "https://transmillas.com/index.php/2021/";
if (URLactual==urlno) {
    // alert(URLactual);
    location.href ='https://www.transmillas.com/';
    
}

</script>

<?php




$title          = "transmillas empresa de carga";


$templateFinal  = implode("", file("./templates/index.html"));
$templateFinal  = str_replace("[TITULO]"           	, $title          , $templateFinal);

$menu_menu    = implode("", file("./templates/menu.html"));
$templateFinal  = str_replace("[MENU]"            	, $menu_menu      , $templateFinal);

$menu_menu    = implode("", file("./templates/footer.html"));
$templateFinal  = str_replace("[FOOTER]"            , $menu_menu      , $templateFinal);

echo $templateFinal;

exit ();


?>


