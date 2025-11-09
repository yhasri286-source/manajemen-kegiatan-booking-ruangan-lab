<?php
class Timeslot {
    private $conn;
    private $table_name = "timeslots";

    public $id;
    public $name;
    public $start_time;
    public $end_time;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create timeslot
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET name=:name, start_time=:start_time, end_time=:end_time";
        $stmt = $this->conn->prepare($query);
        
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->start_time = htmlspecialchars(strip_tags($this->start_time));
        $this->end_time = htmlspecialchars(strip_tags($this->end_time));
        
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":start_time", $this->start_time);
        $stmt->bindParam(":end_time", $this->end_time);
        
        return $stmt->execute();
    }

    // Read all timeslots
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY start_time";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Update timeslot
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET name=:name, start_time=:start_time, end_time=:end_time WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->start_time = htmlspecialchars(strip_tags($this->start_time));
        $this->end_time = htmlspecialchars(strip_tags($this->end_time));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":start_time", $this->start_time);
        $stmt->bindParam(":end_time", $this->end_time);
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute();
    }

    // Delete timeslot
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        return $stmt->execute();
    }

    // Get timeslot by ID
    public function getById() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->name = $row['name'];
            $this->start_time = $row['start_time'];
            $this->end_time = $row['end_time'];
            return true;
        }
        return false;
    }

    // Check if timeslot exists
    public function timeslotExists() {
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE name = :name 
                  AND start_time = :start_time 
                  AND end_time = :end_time 
                  AND id != :id";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":start_time", $this->start_time);
        $stmt->bindParam(":end_time", $this->end_time);
        $stmt->bindParam(":id", $this->id);
        
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
?>