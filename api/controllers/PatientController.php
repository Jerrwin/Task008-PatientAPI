<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Patient.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Validator.php';

class PatientController
{
    private $model;

    public function __construct()
    {
        $database = new Database();
        $db = $database->connect();
        $this->model = new Patient($db);
    }

    // GET /api/patients
    public function index()
    {
        $data = $this->model->getAllPatients();
        Response::send(true, "Patients fetched successfully", $data);
    }

    // GET /api/patients/{id}
    public function show($id)
    {
        $data = $this->model->getPatientById($id);
        if ($data) {
            Response::send(true, "Patient details fetched", $data);
        } else {
            Response::send(false, "Patient not found", [], 404);
        }
    }

    // POST /api/patients
    public function store()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        $error = Validator::validatePatient($data, true);

        if ($error) {
            Response::send(false, $error, [], 400);
        }

        if ($this->model->checkPhoneExists($data['phone'])) {
            Response::send(false, "Phone number already exists", [], 409); // 409 = Conflict
        }

        $newId = $this->model->createPatient($data);

        if ($newId) {
            $newData = $this->model->getPatientById($newId);
            Response::send(true, "Patient created successfully", $newData, 201);
        } else {
            Response::send(false, "Failed to create patient", [], 500);
        }
    }

    // PUT /api/patients/{id}
    public function update($id)
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$this->model->getPatientById($id)) {
            Response::send(false, "Patient not found", [], 404);
        }

        $error = Validator::validatePatient($data, true);

        if ($error) {
            Response::send(false, $error, [], 400);
        }

        // Validate Uniqueness (ONLY if phone is being changed)
        if (isset($data['phone'])) {
            if ($this->model->checkPhoneExistsForUpdate($data['phone'], $id)) {
                Response::send(false, "Phone number already taken by another patient", [], 409);
            }
        }

        if ($this->model->updatePatient($id, $data)) {
            $updatedData = $this->model->getPatientById($id);

            Response::send(true, "Patient updated successfully", $updatedData);
        } else {
            Response::send(false, "Failed to update patient", [], 500);
        }
    }

    // DELETE /api/patients/{id}
    public function destroy($id)
    {
        if (!$this->model->getPatientById($id)) {
            Response::send(false, "Patient not found", [], 404);
        }

        if ($this->model->deletePatient($id)) {
            Response::send(true, "Patient deleted successfully");
        } else {
            Response::send(false, "Failed to delete patient", [], 500);
        }
    }

    // PATCH /api/patients/{id}
    public function patch($id)
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$this->model->getPatientById($id)) {
            Response::send(false, "Patient not found", [], 404);
        }

        $error = Validator::validatePatient($data, false);

        if ($error) {
            Response::send(false, $error, [], 400);
        }

        if (isset($data['phone'])) {
            if ($this->model->checkPhoneExistsForUpdate($data['phone'], $id)) {
                Response::send(false, "Phone number already taken by another patient", [], 409);
            }
        }

        if ($this->model->patchPatient($id, $data)) {

            $updatedData = $this->model->getPatientById($id);

            Response::send(true, "Patient patched successfully", $updatedData);

        } else {
            Response::send(false, "Failed to patch patient", [], 500);
        }
    }

}
?>