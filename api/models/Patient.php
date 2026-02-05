<?php
class Patient
{
    private $conn;
    private $table = "patients";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // 1. Get All Patients
    public function getAllPatients()
    {
        $sql = "SELECT * FROM " . $this->table . " ORDER BY created_at DESC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // 2. Get Single Patient
    public function getPatientById($id)
    {
        $sql = "SELECT * FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // 3. Create Patient
    public function createPatient($data)
    {
        $sql = "INSERT INTO " . $this->table . " (name, age, gender, phone) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("siss", $data['name'], $data['age'], $data['gender'], $data['phone']);

        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    // 4. Update Patient
    public function updatePatient($id, $data)
    {
        // Only update fields that are provided
        $sql = "UPDATE " . $this->table . " SET name=?, age=?, gender=?, phone=? WHERE id=?";
        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("sissi", $data['name'], $data['age'], $data['gender'], $data['phone'], $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // 5. Delete Patient
    public function deletePatient($id)
    {
        $sql = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // 6. Patch Patient (Partial Update)
    public function patchPatient($id, $data)
    {
        $fields = [];
        $params = [];
        $types = "";

        if (isset($data['name'])) {
            $fields[] = "name=?";
            $params[] = $data['name'];
            $types .= "s";
        }
        if (isset($data['age'])) {
            $fields[] = "age=?";
            $params[] = $data['age'];
            $types .= "i";
        }
        if (isset($data['gender'])) {
            $fields[] = "gender=?";
            $params[] = $data['gender'];
            $types .= "s";
        }
        if (isset($data['phone'])) {
            $fields[] = "phone=?";
            $params[] = $data['phone'];
            $types .= "s";
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE " . $this->table . " SET " . implode(", ", $fields) . " WHERE id=?";

        $params[] = $id;
        $types .= "i";

        $stmt = $this->conn->prepare($sql);

        // Use the splat operator (...) to unpack the array into arguments
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Check if phone already exists
    public function checkPhoneExists($phone)
    {
        $sql = "SELECT id FROM " . $this->table . " WHERE phone = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        
        $stmt->store_result();
        
        return $stmt->num_rows > 0;
    }

    public function checkPhoneExistsForUpdate($phone, $currentId)
    {
        $sql = "SELECT id FROM " . $this->table . " WHERE phone = ? AND id != ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $phone, $currentId);
        $stmt->execute();
        $stmt->store_result();
        
        return $stmt->num_rows > 0;
    }

}
?>