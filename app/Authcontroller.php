<?php

session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
function verify_csrf_token() {
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

if (isset($_POST['action']) && $_POST['action'] === 'access') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $controller = new AuthController();
    $controller->access($email, $password);
}

class AuthController {
    public function access($email, $password) {
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://crud.jonathansoto.mx/api/login',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'email' => $email,
                'password' => $password
            ),
            CURLOPT_FOLLOWLOCATION => true,
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $response = json_decode($response);

        

        if (isset($response->message) && $response->message === "Registro obtenido correctamente" && is_object($response->data)) {
            header('Location: ../products.html');
        } else {
            header('Location: ../index.html');
        }

        
    }
}
