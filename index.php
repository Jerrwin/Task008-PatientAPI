<?php
require_once 'middlewares/JsonMiddleware.php';
require_once 'controllers/PatientController.php';

JsonMiddleware::handle();

$requestUri = isset($_GET['request']) ? explode('/', $_GET['request']) : [];
$resource = isset($requestUri[0]) ? $requestUri[0] : '';
$id = isset($requestUri[1]) ? $requestUri[1] : null;

$method = $_SERVER['REQUEST_METHOD'];

if ($resource === 'patients') {

    $controller = new PatientController();

    switch ($method) {
        case 'GET':
            if ($id) {
                // GET /api/patients/{id}
                $controller->show($id);
            } else {
                // GET /api/patients
                $controller->index();
            }
            break;

        case 'POST':
            // POST /api/patients
            $controller->store();
            break;

        case 'PUT':
            // PUT /api/patients/{id}
            if ($id) {
                $controller->update($id);
            } else {
                echo json_encode(["status" => false, "message" => "ID required for Update"]);
            }
            break;

        case 'DELETE':
            // DELETE /api/patients/{id}
            if ($id) {
                $controller->destroy($id);
            } else {
                echo json_encode(["status" => false, "message" => "ID required for Delete"]);
            }
            break;

        case 'PATCH':
            // PATCH /api/patients/{id}
            if ($id) {
                $controller->patch($id);
            } else {
                echo json_encode(["status" => false, "message" => "ID required for Patch"]);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(["status" => false, "message" => "Method Not Allowed"]);
            break;
    }

} else {
    // 404 Route Not Found
    http_response_code(404);
    echo json_encode(["status" => false, "message" => "Endpoint not found"]);
}
?>