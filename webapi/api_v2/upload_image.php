<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../config/database.php';
include_once '../class/absensi.php';

$database = new Database();
$db = $database->getConnection();

$item = new Absensi($db);

// $data = json_decode(file_get_contents("php://input"));
// var_dump($_FILES['fileToUpload']);
// var_dump($data); die();

$item = new Absensi($db);
// var_dump($_GET['uid']);
// var_dump($item); 
// die();
$item->id = isset($_GET['id']) ? $_GET['id'] : die('wrong structure!');

// if (isset($data->uid)) {
    // $item->uid = $data->uid;
    
    // Panggil fungsi isCardRegistered
    // $isRegistered = $item->isCardRegistered();
    
    // if ($isRegistered) {
        // Proses Upload Image
        $target_dir = "../../uploads/";
        $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is a actual image or fake image
        if(isset($_POST["submit"])) {
            $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
            if($check !== false) {
                $uploadOk = 1;
            } else {
                $response = array("status" => "error", "message" => "File is not an image.");
                http_response_code(400);
                echo json_encode($response);
                exit();
            }
        }

        // Check if file already exists
        if (file_exists($target_file)) {
            $response = array("status" => "error", "message" => "Sorry, file already exists.");
            http_response_code(400);
            echo json_encode($response);
            exit();
        }

        // Check file size
        if ($_FILES["fileToUpload"]["size"] > 500000) { // 500KB limit
            $response = array("status" => "error", "message" => "Sorry, your file is too large.");
            http_response_code(400);
            echo json_encode($response);
            exit();
        }

        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
            $response = array("status" => "error", "message" => "Sorry, only JPG, JPEG, PNG & GIF files are allowed.");
            http_response_code(400);
            echo json_encode($response);
            exit();
        }

        // Try to upload file
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            $fileName = htmlspecialchars(basename($_FILES["fileToUpload"]["name"]));

            if($item->updateImageName($item->id, $fileName)){
                // create array
                $data_arr = array(
                    "image_name" => $item->image_name,
                );
                http_response_code(200);
                var_dump($data_arr);
                echo json_encode($data_arr);
            } else{
                http_response_code(404);
                echo json_encode("Failed!");
            }
        } else {
            $response = array("status" => "error", "message" => "Sorry, there was an error uploading your file.");
            http_response_code(500);
            echo json_encode($response);
        }
    // } else {
    //     $response = array("status" => "error", "message" => "Kartu tidak terdaftar.");
    //     http_response_code(404);
    //     echo json_encode($response);
    // }
// } else {
//     $response = array("status" => "error", "message" => "Invalid request structure.");
//     http_response_code(400);
//     echo json_encode($response);
// }
?>
