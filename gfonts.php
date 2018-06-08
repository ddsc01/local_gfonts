<?php
/***
 * -- Local GFonts --
 *
 * Wrapper to load Googlefonts from local Server
 * Downloads Fonts & CSS
 *
 * usage:
 * change your googlefont link from https://fonts.googleapis.com/css?XYZ to /gfonts/gfonts.php?XYZ
 */

// Parse request to string
if(isset($_GET['subset'])) $_GET['family'] .= '&subset='.$_GET['subset'];
$font_family = urlencode(urldecode($_GET['family']));

// parsed final css filename
$md5css = dirname(__FILE__)."/fonts/".md5($font_family).".css";

if(file_exists($md5css)) {
    // if css is already there, just load
    $content = file_get_contents($md5css);
} else {
    // get gfonts.php frontend url
    $local_fonts_url = $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
    $local_fonts_url = substr($local_fonts_url, 0, strpos($local_fonts_url, 'gfonts.php'));

    // get css from google first
    $gfont_css_url = "https://fonts.googleapis.com/css?family=";
    $content = file_get_contents($gfont_css_url . $font_family);
    $exp = explode("\n", $content);

    // parse for font-urls
    foreach ($exp as $line) {
        if (strpos($line, 'url(') !== false) {
            $files[] = get_string_between($line, 'url(', ')');
        }
    }

    // download fonts to local server and modify google-css
    foreach ($files as $file) {
        $exp = explode("/", $file);
        $rfile = $exp[count($exp) - 1];
        if (!file_exists(dirname(__FILE__) . "/" . $rfile)) {
            file_put_contents(dirname(__FILE__) . "/fonts/" . $rfile, fopen($file, 'r'));
        }
        $content = str_replace($file, "https://{$local_fonts_url}fonts/{$rfile}", $content);
    }

    // write md5ed local css file
    file_put_contents($md5css,$content);
}
// return "local" css
header("Content-type: text/css");
echo $content;

// helper to parse css
function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}