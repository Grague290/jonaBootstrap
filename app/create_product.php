<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Verificar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['code' => 'ERROR', 'message' => 'Método no permitido']);
    exit;
}

// Verificar campos requeridos
$requiredFields = ['name', 'slug', 'description', 'features'];
foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        echo json_encode(['code' => 'ERROR', 'message' => "El campo $field es requerido"]);
        exit;
    }
}

// Preparar los datos para la API
$data = [
    'name' => $_POST['name'],
    'slug' => $_POST['slug'],
    'description' => $_POST['description'],
    'features' => $_POST['features'],
    'brand_id' => '1', // Asumiendo un valor por defecto, ajusta según sea necesario
    'categories' => '1', // Asumiendo un valor por defecto, ajusta según sea necesario
];

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://crud.jonathansoto.mx/api/products',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $data,
    CURLOPT_HTTPHEADER => array(
        'Authorization: Bearer 54|RkGbFKaDq0sBDeKQdAuqYq5AFORWXXmjUTVo9lrw'
    ),
));

$response = curl_exec($curl);

if ($response === false) {
    echo json_encode(['code' => 'ERROR', 'message' => curl_error($curl)]);
} else {
    $responseData = json_decode($response, true);
    if (isset($responseData['code']) && $responseData['code'] === "OK") {
        echo json_encode(['code' => 'OK', 'message' => 'Producto creado exitosamente']);
    } else {
        echo json_encode(['code' => 'ERROR', 'message' => $responseData['message'] ?? 'Error desconocido']);
    }
}

curl_close($curl);