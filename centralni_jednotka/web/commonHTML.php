<?php

//inspired by https://getbootstrap.com/docs/4.0/components/navbar/#supported-content
function printHeader($fluid)
{
    $RPI_URL = "http://192.168.1.5/bp";
    $container = "container";
    if($fluid)
        $container = "container-fluid";
    echo "
<header>
    <nav class=\"navbar navbar-expand-lg navbar-dark bg-dark\">
        <div class=\"$container\">
            <a class=\"navbar-brand\" href=$RPI_URL><strong>Domácnost</strong></a>
            <button class=\"navbar-toggler\" type=\"button\" data-toggle=\"collapse\" data-target=\"#navbarSupportedContent\" aria-controls=\"navbarSupportedContent\" aria-expanded=\"false\" aria-label=\"Toggle navigation\">
            <span class=\"navbar-toggler-icon\"></span>
            </button>
            
            <div class=\"collapse navbar-collapse\" id=\"navbarSupportedContent\">
                <ul class=\"navbar-nav mr-auto\">
                  <li class=\"nav-item active\">
                    <a class=\"nav-link\" href=\"$RPI_URL/rules.php\"><strong>Automatická pravidla</strong><span class=\"sr-only\">(current)</span></a>
                  </li>
                  <li class=\"nav-item active\">
                    <a class=\"nav-link\" href=\"$RPI_URL/settings.php\"><strong>Moduly</strong><span class=\"sr-only\">(current)</span></a>
                </ul>
            </div>
        </div>
    </nav>
</header>";
}