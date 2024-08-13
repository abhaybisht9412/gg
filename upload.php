<?php
require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

// AWS S3 configuration
$bucketName = 'your-bucket-name';
$region = 'your-region'; // e.g., 'us-east-1'
$accessKey = 'your-access-key';
$secretKey = 'your-secret-key';

// Create an S3 client
$s3 = new S3Client([
    'region' => $region,
    'version' => 'latest',
    'credentials' => [
        'key' => $accessKey,
        'secret' => $secretKey,
    ]
]);

// Directory where files will be temporarily uploaded before moving to S3
$target_dir = "uploads/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

// Check if file already exists
if (file_exists($target_file)) {
    echo "Sorry, file already exists.";
    $uploadOk = 0;
}

// Check file size (limit to 5MB)
if ($_FILES["fileToUpload"]["size"] > 5000000) {
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
}

// Allow certain file formats
$allowedTypes = ['jpg', 'png', 'jpeg', 'gif', 'pdf', 'docx'];
if (!in_array($fileType, $allowedTypes)) {
    echo "Sorry, only JPG, JPEG, PNG, GIF, PDF, & DOCX files are allowed.";
    $uploadOk = 0;
}

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
} else {
    // Attempt to upload the file
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        echo "The file ". htmlspecialchars(basename($_FILES["fileToUpload"]["name"])) . " has been uploaded.";

        // Upload the file to the S3 bucket
        try {
            $s3->putObject([
                'Bucket' => $bucketName,
                'Key' => basename($target_file),
                'SourceFile' => $target_file,
                'ACL' => 'public-read', // You can change this to private or other permissions
            ]);
            echo " and successfully uploaded to S3.";
        } catch (S3Exception $e) {
            echo "Error uploading to S3: " . $e->getMessage();
        }

        // Optionally, delete the local file after uploading to S3
        unlink($target_file);

    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}
?>
