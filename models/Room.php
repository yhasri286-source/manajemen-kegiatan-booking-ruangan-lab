<?php
class Room {
    private $conn;
    private $table_name = "rooms";

    public $id;
    public $name;
    public $description;
    public $capacity;
    public $facilities;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET name=:name, description=:description, capacity=:capacity, facilities=:facilities";
        $stmt = $this->conn->prepare($query);
        
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->capacity = htmlspecialchars(strip_tags($this->capacity));
        $this->facilities = htmlspecialchars(strip_tags($this->facilities));
        
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":capacity", $this->capacity);
        $stmt->bindParam(":facilities", $this->facilities);
        
        return $stmt->execute();
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " SET name=:name, description=:description, capacity=:capacity, facilities=:facilities WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->capacity = htmlspecialchars(strip_tags($this->capacity));
        $this->facilities = htmlspecialchars(strip_tags($this->facilities));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":capacity", $this->capacity);
        $stmt->bindParam(":facilities", $this->facilities);
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        return $stmt->execute();
    }

    public function getAvailableRooms($date, $start_time, $end_time) {
        $query = "SELECT r.* FROM " . $this->table_name . " r 
                  WHERE r.id NOT IN (
                      SELECT b.room_id FROM bookings b 
                      WHERE b.date = :date 
                      AND b.status = 'approved'
                      AND (
                          (b.start_time < :end_time AND b.end_time > :start_time)
                      )
                  )";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":date", $date);
        $stmt->bindParam(":start_time", $start_time);
        $stmt->bindParam(":end_time", $end_time);
        $stmt->execute();
        return $stmt;
    }

    public function getById() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->description = $row['description'];
            $this->capacity = $row['capacity'];
            $this->facilities = $row['facilities'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }

        public function roomExists() {
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE name = :name 
                  AND id != :id";
        $stmt = $this->conn->prepare($query);
        
        $this->name = htmlspecialchars(strip_tags($this->name));
        
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":id", $this->id);
        
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}

?>