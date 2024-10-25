<?php
date_default_timezone_set('Asia/Jakarta');
class Absensi{
	// Connection
	private $conn;

	// Table
	private $db_table = "data_absen";
	private $db_table1 = "data_karyawan";
	private $db_table2 = "data_invalid";

	// Columns
	public $id;
	public $tanggal;
	public $waktu;
	public $uid;
	public $status;
	public $last_status;
	public $nama;

	// Db connection
	public function __construct($db){
		$this->conn = $db;
	}

	// CREATE
	public function createData(){
	//1. Cek user
		$sqlQuery = "SELECT * FROM ". $this->db_table1 ." WHERE uid = :uid LIMIT 0,1";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->bindParam(":uid", $this->uid);
		$stmt->execute();
		if($stmt->errorCode() == 0) {
			while(($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) != false) {
				$this->nama = $dataRow['nama'];
			}
		} else {
			$errors = $stmt->errorInfo();
			echo($errors[2]);
		}
		$itemCount = $stmt->rowCount();
		
		if($itemCount > 0){
			//UID terdaftar -> cek status terakhir
			$sqlQuery = "SELECT data_absen.id, data_absen.uid, data_absen.status, data_karyawan.nama 
						FROM ". $this->db_table .", ". $this->db_table1 ."
						WHERE data_absen.id = (SELECT MAX(data_absen.id) 
						FROM ". $this->db_table ." WHERE data_absen.uid = :uid) 
						AND data_karyawan.uid= :uid";
			$stmt = $this->conn->prepare($sqlQuery);
			$stmt->bindParam(":uid", $this->uid);
			$stmt->execute();
			$itemCount = $stmt->rowCount();
			if($itemCount > 0){
				//error handling
				if($stmt->errorCode() == 0) {
					while(($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) != false) {
						$this->last_status = $dataRow['status'];
						$this->nama = $dataRow['nama'];
						//echo($this->last_status);
					}
				} else {
					$errors = $stmt->errorInfo();
					echo($errors[2]);
				}
			}else{
				$this->last_status ="OUT";
			}
			
			//set status
			if ($this->last_status == "IN"){
				$this->status = "OUT";
			}else{
				$this->status= "IN";
			}
			//Insert Data to data_absen	
			$sqlQuery = "INSERT INTO ". $this->db_table ."
					SET	waktu = :waktu, uid = :uid, status = :now_status";
						
			$this->waktu = date("H:i:s");
			
			$stmt = $this->conn->prepare($sqlQuery);
		
			// sanitize
			$this->uid=htmlspecialchars(strip_tags($this->uid));
		
			// bind data
			$stmt->bindParam(":uid", $this->uid);
			$stmt->bindParam(":now_status", $this->status);
			$stmt->bindParam(":waktu", $this->waktu);
		
			if($stmt->execute()){
			   return true;
			}
			return false;
		}
		else{
			//UID tidak terdaftar
			$this->status= "INVALID";
			$this->nama ="Invalid";
			
			//Insert Data to data_invalid	
			$sqlQuery = "INSERT INTO
						". $this->db_table2 ."
					SET
						waktu = :waktu,
						uid = :uid, 
						status = :now_status";
			$this->waktu = date("H:i:s");
			
			$stmt = $this->conn->prepare($sqlQuery);
		
			// sanitize
			$this->uid=htmlspecialchars(strip_tags($this->uid));
		
			// bind data
			$stmt->bindParam(":uid", $this->uid);
			$stmt->bindParam(":now_status", $this->status);
			$stmt->bindParam(":waktu", $this->waktu);
		
			if($stmt->execute()){
			   return true;
			}
			return false;
			
		}
		
	}

	public function isCardRegistered() {
        // Query SQL untuk memeriksa keberadaan UID kartu di dalam tabel
        $query = "SELECT COUNT(*) as count FROM data_karyawan WHERE uid = :uid";

        // Persiapkan statement query
        $stmt = $this->conn->prepare($query);

        // Bind parameter
        $stmt->bindParam(':uid', $this->uid);

        // Eksekusi query
        $stmt->execute();

        // Ambil hasil query
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Periksa jumlah baris yang ditemukan
        if ($row['count'] > 0) {
            return true; // Kartu terdaftar
        } else {
            return false; // Kartu tidak terdaftar
        }
    }

	public function getLatestEntry() {
		$query = "SELECT * FROM data_absen ORDER BY waktu DESC LIMIT 1";
	
		// Prepare the query statement
		$stmt = $this->conn->prepare($query);
	
		// Execute the query
		$stmt->execute();
	
		// Fetch the result
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		// var_dump(123456789000000000000000);
		// var_dump($row);
	
		return $row; // Added the missing semicolon
	}
	

	public function updateImageName($id, $image_name) {
        // Query untuk memperbarui nama gambar berdasarkan ID
        $query = "UPDATE data_absen SET image_name = :image_name WHERE id = :id";

        // Siapkan statement
        $stmt = $this->conn->prepare($query);

        // Bersihkan data
        $image_name = htmlspecialchars(strip_tags($image_name));
        $id = htmlspecialchars(strip_tags($id));

        // Bind parameter
        $stmt->bindParam(':image_name', $image_name);
        $stmt->bindParam(':id', $id);

        // Eksekusi statement dan kembalikan hasil
        if ($stmt->execute()) {
            return true; // Pembaruan berhasil
        }

        return false; // Pembaruan gagal
    }

}
?>