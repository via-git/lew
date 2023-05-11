<?php
// SEMUA FUNGSI
$api_key = $argv[2];
$key_pass = $argv[3];
$ttd = $argv[4];

function getj($url) {
    $jsonData = file_get_contents($url);
    // Mendekode string JSON menjadi array
    $data = json_decode($jsonData, true);
    return $data;
}

function rgen($angka) {
    $stringAngka = strval($angka);  // Mengonversi angka ke string
    $jumlahDesimal = preg_match('/\.\d+/', $stringAngka, $matches);
    $hasil = strlen($matches[0]) - 1;  // Output: 5
    return $hasil;
}

function gco() {
    $prefix = 'id_';
    $suffix = '_order';
    $timestamp = round(microtime(true) * 1000);
    $rand_string = bin2hex(random_bytes(5));
    $client_oid = $prefix . $rand_string . $suffix;
    return $client_oid;
}

function order($pair, $side, $lev, $price, $size) {
    // BUKA POSISI
    $now = round(microtime(true) * 1000); 
    $url = 'https://api-futures.kucoin.com/api/v1/orders';
    $data = array(
        "clientOid" => gco(),
        "symbol" => $pair, 
        "side" => $side, 
        "type" => "limit",
        "leverage" => $lev,
        "price" => $price,
        "size" => $size
        );
    $data_json = json_encode($data);
    $str_to_sign = $now . 'POST' . '/api/v1/orders' . $data_json;
    $signature = base64_encode(hash_hmac('sha256', $str_to_sign, $ttd, true));
    $headers = array(
        'KC-API-SIGN:'.$signature,
        'KC-API-TIMESTAMP:'.$now,
        'KC-API-KEY:$api_key',
        'KC-API-PASSPHRASE:$key_pass',
        'Content-Type:application/json'
        );
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function cek($tr, $br) {
    $url = "http://api-futures.kucoin.com/api/v1/contracts/active";
    $jsonData = file_get_contents($url);
    // Mendekode string JSON menjadi array
    $data = json_decode($jsonData, true);
    // Mengambil data yang memenuhi kriteria
    $results = array();
    foreach ($data['data'] as $item) {
        $symbol = $item['symbol'];
        $priceChgPct = $item['priceChgPct'];
        $maxLeverage = $item['maxLeverage'];
        $lot = $item['multiplier'];
        $mark = $item['markPrice'];
        $idx = $item['volumeOf24h'] * $item['markPrice'];
        if ((strpos($symbol, 'USDT') !== false) && ($priceChgPct < -0.05 || $priceChgPct > 0.05 ) && ($idx > 1000000) && ($mark > 0.0001)) {
            $results[] = array(
                'symbol' => $symbol,
                'priceChgPct' => $priceChgPct,
                'lev' => $maxLeverage,
                'lot'=> $lot,
                'idx'=> $idx
                );
        }
    }
    // Mengurutkan hasil berdasarkan priceChgPct
    usort($results, function($a, $b) {
        return $b['idx'] <=> $a['idx'];
    });
    // Mengambil 3 hasil dengan priceChgPct tertinggi dan terendah
    $topResults = array_slice($results, 0, $tr);
    $bottomResults = array_slice($results, $br);
    // Menggabungkan hasil tertinggi dan terendah
    $finalResults = array_merge($topResults, $bottomResults);
    return $finalResults;
}

function detail($pair, $data2, $data1, $bal, $lot) {
    $asks = $data1['data']['asks'][0][0];
    $bids = $data1['data']['bids'][0][0];
    $r = rgen($asks);
    $op = $data2['data'][0][1];
    $cl = $data2['data'][3][4];
    $chg = round(($cl - $op) / $op * 100, 2);
    if ($chg > 0) {
        $lev = round(10 / ($chg / 10), 0);
        
    }else{
        $lev = round(10 / ($chg / -10), 0);
        
    }
    if ($chg > 0) {
        $tp = round($bids - ($chg / 1000 * $asks), $r);
    }else{
        $tp = round($asks + ($chg / 1000 * $bids * -1), $r);
    }
    $amount = round($bal / $cl * $lev / $lot, 0);
    echo 'Pair                   : '.$pair.'
';
    echo '1 Jam Pergerakan Harga : '.$chg.' %
';
    echo 'Modal                  : '.$bal.'
';
    echo 'Leverage : '.$lev.'
';
    echo 'Harga-Buka             : '.$op.'
';
    echo 'Harga-Tutup            : '.$cl.'
';
    $res = array($asks, $bids, $chg, $tp, $amount, $lev);
    return $res;
}

?>