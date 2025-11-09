<?php
class Booking {
    private $conn;
    private $table_name = "bookings";

    public $id;
    public $user_id;
    public $room_id;
    public $date;
    public $start_time;
    public $end_time;
    public $purpose;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        if($this->hasTimeConflict()) {
            return false;
        }

        $query = "INSERT INTO " . $this->table_name . " 
                  SET user_id=:user_id, room_id=:room_id, date=:date, 
                  start_time=:start_time, end_time=:end_time, purpose=:purpose, status='pending'";
        $stmt = $this->conn->prepare($query);
        
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->room_id = htmlspecialchars(strip_tags($this->room_id));
        $this->date = htmlspecialchars(strip_tags($this->date));
        $this->start_time = htmlspecialchars(strip_tags($this->start_time));
        $this->end_time = htmlspecialchars(strip_tags($this->end_time));
        $this->purpose = htmlspecialchars(strip_tags($this->purpose));
        
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":room_id", $this->room_id);
        $stmt->bindParam(":date", $this->date);
        $stmt->bindParam(":start_time", $this->start_time);
        $stmt->bindParam(":end_time", $this->end_time);
        $stmt->bindParam(":purpose", $this->purpose);
        
        return $stmt->execute();
    }

    public function hasTimeConflict() {
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE room_id = :room_id 
                  AND date = :date 
                  AND status = 'approved'
                  AND (
                      (start_time < :end_time AND end_time > :start_time)
                  )";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":room_id", $this->room_id);
        $stmt->bindParam(":date", $this->date);
        $stmt->bindParam(":start_time", $this->start_time);
        $stmt->bindParam(":end_time", $this->end_time);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }

    public function read() {
        $query = "SELECT b.*, r.name as room_name, u.username as user_name 
                  FROM " . $this->table_name . " b
                  LEFT JOIN rooms r ON b.room_id = r.id
                  LEFT JOIN users u ON b.user_id = u.id
                  ORDER BY b.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . " SET status=:status WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        return $stmt->execute();
    }
}
?>