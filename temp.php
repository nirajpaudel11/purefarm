<!DOCTYPE html>
<html>
<head>
    <title>Upload Image</title>
</head>
<body>
    <form method="POST" action="upload.php" enctype="multipart/form-data">
        <label>Select Image:</label>
        <input type="file" name="image" required>
        <br>
        <button type="submit" name="upload">Upload Image</button>
    </form>
</body>
</html>
