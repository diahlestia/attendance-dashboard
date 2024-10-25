<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../config/database.php';
include_once '../class/absensi.php';

$database = new Database();
$db = $database->getConnection();

$item = new Absensi($db);

if (isset($_POST['foto'])) {
    $foto = $_POST['foto'];
    $id = $_POST['id'];

    // Get the latest entry in the database
    $latestEntry = $item->getLatestEntry(); // Ensure this function is defined in your Absensi class

    // var_dump($foto, $id);

    if (!$latestEntry) {
        http_response_code(500);
        echo json_encode(array("status" => "error", "message" => "Failed to retrieve latest entry from database."));
        exit();
    }

    $item->id = $latestEntry['id']; // Assuming 'id' is a key in the returned entry

    // Decode base64 string to image data
    $image_data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $foto));
    
    // Generate unique file name for the image
    $image_file = "image_" . uniqid() . ".jpg";
    $item->image_name = $foto; // Assuming 'id' is a key in the returned entry
    // var_dump($item->id, $item->image_name);

    // Path to store the image
    $target_dir = "../../uploads/";
    $target_file = $target_dir . $image_file;

    // Save the image data to the file
    if (file_put_contents($target_file, $image_data)) {
        // Update the database with the image file name
        if ($item->updateImageName($item->id, $item->image_name)) { // Update using the latest ID
            http_response_code(200);
            echo json_encode(array("status" => "success", "message" => "Image uploaded successfully."));
        } else {
            http_response_code(500);
            echo json_encode(array("status" => "error", "message" => "Failed to update image name in the database."));
        }
    } else {
        http_response_code(500);
        echo json_encode(array("status" => "error", "message" => "Failed to save the image file."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("status" => "error", "message" => "Image data not provided."));
}
?>
