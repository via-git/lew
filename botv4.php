<?php
include('fungsi.php');
$bal = $argv[1];

// Mulai
$view = cek(2, -2);

// kode pertama
for ($i = 0; $i < count($view); $i++) {
    $result = $view[$i];
    $pair = $result['symbol'];
    $lot = $result['lot'];
    $now = round(microtime(true) * 1000);
    $tha = $now - 4 * 60 * 60 * 1000;
    $url1 = "https://api-futures.kucoin.com/api/v1/kline/query?symbol=" . $pair . "&granularity=60&from=" . $tha . "&to=" . $now;
    $url2 = "https://api-futures.kucoin.com/api/v1/level2/depth20?symbol=" . $pair;
    $dat1 = getj($url1);
    $dat2 = getj($url2);
    //Perhitungan Rumit
    $hasil = detail($pair, $dat1, $dat2, $bal, $lot);
    $asks = $hasil[0];
    $bids = $hasil[1];
    $chg = $hasil[2];
    $tp = $hasil[3];
    $size = $hasil[4];
    $lev = $hasil[5];
    // Open Posisi
    if ($chg > 5) {
        // OP
        echo 'Open Short 
';
        echo order($pair, 'sell', $lev, $bids, $size);
        echo '
';
        // CL
        echo order($pair, 'buy', $lev, $tp, $size);
        echo '
';
    }
    if ($chg < -5) {
        // OP
        echo 'Open Long 
';
        echo order($pair, 'buy', $lev, $asks, $size);
        echo '
';
        // CL
        echo order($pair, 'sell', $lev, $tp, $size);
        echo '
';
    }

}



?>