<?php
// common functions used by several scripts

// http://es1.php.net/manual/en/function.strip-tags.php#110280
// better than strip_tags() because "<p>Mål</p>Dette er" --> "Mål Dette er"
// would be "MålDette er" with strip_tags
function rip_tags($string) {    
    // ----- remove HTML TAGs ----- 
    $string = preg_replace ('/<[^>]*>/', ' ', $string); 
    
    // ----- remove control characters ----- 
    $string = str_replace("\r", '', $string);    // --- replace with empty space
    $string = str_replace("\n", ' ', $string);   // --- replace with space
    $string = str_replace("\t", ' ', $string);   // --- replace with space
    $string = str_replace(chr(0xC2).chr(0xA0), ' ', $string); // --- converts no-break-space to normal space
    
    // ----- remove multiple spaces ----- 
    $string = trim(preg_replace('/ {2,}/', ' ', $string));
    
    return $string; 
}

?>