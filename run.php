<?php

require "vendor/autoload.php";

include "app/Magneto2Client.php";

$base_url = "http://develop.bents.pushonltd.co.uk/";
$username = "pushon";
$password = "gen3g6d2h6jarxnx";

if ($handle = fopen('input/input.csv', "r")) {
    $client = new \App\Magneto2Client($base_url);
    $client->generateToken($username, $password);
    file_put_contents("output/rewrites.csv", "", 0);

    $line = 1;
    while ($data = fgetcsv($handle)) {
        $lineNumberText = getLineNumberText($line++);
        $text = "[{$lineNumberText}] Updating: {$data[0]}";
        echo "{$text}...";
        $product = $client->getProduct($data[0]);
        if($data[1] == "" || $data[1] == null) {
            $res = $client->updateUrlKey($product, $data[1]);
        } else {
            $res = $client->updateUrlKey($product, false);
        }

        if($res->getStatusCode() == 200) {
            $cap = _withColor("OK   ");
        } else {
            $cap = _withColor("ERROR", "0;30", "41");
        }
        echo "\r{$text} :: {$cap}";
    }
}

function getLineNumberText($number, $max = 10000)
{
    $spaces = 0;
    while ($max > 10) {
        if ($number < ($max /= 10)) {
            $spaces++;
        }
    }
    return $number . str_repeat(" ", $spaces);
}

function _withColor($string, $fg = "0;30", $bg = "42")
{
    $color = "";
    $cap = "\033[0m\n";
    if ($fg) {
        $color .= "\033[{$fg}m";
    }
    if ($bg) {
gst        $color .= "\033[{$bg}m";
    }
    return $color . $string . $cap;
}


