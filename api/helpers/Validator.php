<?php
class Validator
{
    public static function validatePatient($data, $isStrict = true)
    {
        if ($isStrict) {
            if (empty($data['name']) || empty($data['age']) || empty($data['gender']) || empty($data['phone'])) {
                return "All fields (name, age, gender, phone) are required";
            }
        }

        if (isset($data['age'])) {
            if (!is_numeric($data['age']) || $data['age'] < 0 || $data['age'] > 120) {
                return "Invalid Age provided (must be 0-120)";
            }
        }

        if (isset($data['gender'])) {
            $allowedGenders = ['Male', 'Female', 'Other'];
            if (!in_array(ucfirst($data['gender']), $allowedGenders)) {
                return "Gender must be Male, Female, or Other";
            }
        }

        if (isset($data['phone'])) {
            if (!preg_match('/^[0-9]{10}$/', $data['phone'])) {
                return "Phone number must be exactly 10 digits";
            }
        }

        return null;
    }
}
?>