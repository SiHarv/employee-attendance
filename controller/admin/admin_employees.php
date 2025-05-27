<?php
require_once '../../db/connect.php';

class Employees {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAllEmployees() {
        $query = "SELECT * FROM employees";
        $stmt = $this->db->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addEmployee($name, $email, $position) {
        $query = "INSERT INTO employees (name, email, position) VALUES (:name, :email, :position)";
        $stmt = $this->db->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':position', $position);
        return $stmt->execute();
    }

    public function updateEmployee($id, $name, $email, $position) {
        $query = "UPDATE employees SET name = :name, email = :email, position = :position WHERE id = :id";
        $stmt = $this->db->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':position', $position);
        return $stmt->execute();
    }

    public function deleteEmployee($id) {
        $query = "DELETE FROM employees WHERE id = :id";
        $stmt = $this->db->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}

$employees = new Employees();
?>