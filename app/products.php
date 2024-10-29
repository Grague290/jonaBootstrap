<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://crud.jonathansoto.mx/api/products',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
        'Authorization: Bearer 54|RkGbFKaDq0sBDeKQdAuqYq5AFORWXXmjUTVo9lrw'
    ),
));

$response = curl_exec($curl);
curl_close($curl);

$products = json_decode($response, true);

if (isset($products['data'])) {
    echo json_encode($products);
} else {
    echo json_encode(['error' => 'No se pudieron obtener los productos']);
}
?>