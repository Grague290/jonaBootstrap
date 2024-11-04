<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

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

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}
class ProductController {
    private $apiUrl = 'https://crud.jonathansoto.mx/api/products';
    private $token = '111|3S2Pd6LSVTvtacg2EW634JQLOyVgXNI40JgsWexV';


    public function getAll() {
        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $this->token
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            
            if ($err) {
                throw new Exception('Error de cURL: ' . $err);
            }

            $data = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Respuesta no válida del servidor');
            }

            echo $response;
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage());
        }
    }


    public function create($data) {
        if (!verify_csrf_token()) {
            $this->sendErrorResponse('Token CSRF inválido', 403);
            return;
        }
        if (empty($data['name']) || empty($data['slug']) || empty($data['brand_id'])) {
            $this->sendErrorResponse('Campos obligatorios faltantes', 400);
            return;
        }

        try {
            $curl = curl_init();
            
            $postFields = array(
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'],
                'features' => $data['features'],
                'brand_id' => $data['brand_id']
            );

            if (isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {
                $postFields['cover'] = new CURLFile(
                    $_FILES['cover']['tmp_name'],
                    $_FILES['cover']['type'],
                    $_FILES['cover']['name']
                );
            }

            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->apiUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $postFields,
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $this->token
                ),
            ));
    
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
    
            if ($err) {
                throw new Exception('Error de cURL: ' . $err);
            }
    
            $responseData = json_decode($response, true);
            if ($responseData['code'] === 'OK') {
                echo json_encode([
                    'code' => 'OK',
                    'message' => 'Producto creado exitosamente'
                ]);
            } else {
                throw new Exception($responseData['message'] ?? 'Error al crear el producto');
            }
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage());
        }
    }

    public function update($data) {
        if (!verify_csrf_token()) {
            $this->sendErrorResponse('Token CSRF inválido', 403);
            return;
        }
        if (empty($data['id']) || empty($data['name'])) {
            $this->sendErrorResponse('Campos obligatorios faltantes', 400);
            return;
        }

        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->apiUrl . '/' . $data['id'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_POSTFIELDS => http_build_query($data),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/x-www-form-urlencoded',
                    'Authorization: Bearer ' . $this->token
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                throw new Exception('Error de cURL: ' . $err);
            }

            if (curl_getinfo($curl, CURLINFO_HTTP_CODE) !== 200) {
                throw new Exception('Error en la solicitud de actualización');
            }

            $responseData = json_decode($response, true);
            if ($responseData['code'] === 'OK') {
                echo json_encode([
                    'code' => 'OK',
                    'message' => 'Producto actualizado exitosamente'
                ]);
            } else {
                throw new Exception($responseData['message'] ?? 'Error al actualizar el producto');
            }
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage());
        }
    }

    public function delete($id) {
        if (!verify_csrf_token()) {
            $this->sendErrorResponse('Token CSRF inválido', 403);
            return;
        }
        try {
            if (!$id) {
                throw new Exception('ID de producto no proporcionado');
            }

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->apiUrl . '/' . $id, // Corrección aquí
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'DELETE',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $this->token
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                throw new Exception('Error de cURL: ' . $err);
            }

            $responseData = json_decode($response, true);
            if ($responseData['code'] === 'OK') {
                echo json_encode([
                    'code' => 'OK',
                    'message' => 'Producto eliminado exitosamente'
                ]);
            } else {
                throw new Exception($responseData['message'] ?? 'Error al eliminar el producto');
            }
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage());
        }
    }

    private function sendErrorResponse($message, $code = 500) {
        http_response_code($code);
        echo json_encode([
            'code' => 'ERROR',
            'message' => $message
        ]);
    }

    public function getBrands() {
        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://crud.jonathansoto.mx/api/brands',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $this->token
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            echo $response;
        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage());
        }
    }
}

try {
    $productController = new ProductController();

    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'getAll':
                $productController->getAll();
                break;
            case 'getBrands':
                $productController->getBrands();
                break;
            case 'delete':
                if (!verify_csrf_token()) {
                    throw new Exception('Token CSRF inválido');
                }
                $productController->delete($_GET['id']);
                break;
            default:
                throw new Exception('Acción no encontrada');
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verify_csrf_token()) {
            throw new Exception('Token CSRF inválido');
        }
        $productController->create(array_merge($_POST, $_FILES));
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        if (!verify_csrf_token()) {
            throw new Exception('Token CSRF inválido');
        }
        parse_str(file_get_contents("php://input"), $_PUT);
        $productController->update($_PUT);
    } else {
        throw new Exception('Método no permitido');
    }
} catch (Exception $e) {
    //$productController->sendErrorResponse($e->getMessage());
}