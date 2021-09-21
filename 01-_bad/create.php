<?php
require_once "functions.php";

$pdo = new PDO('mysql:host=localhost;port=3306;dbname=products_crud', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


$errors = [];
$title = '';
$price = '';
$description = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $date = date('Y-m-d H:i:s');

    if (!$title) {
        $errors[] = 'Product title is required';
    }
    if (!$price) {
        $errors[] = 'Product price is required';
    }

    if (empty($errors)) {
        $image = $_FILES['image'] ?? null;
        $imagePath = '';

        if (!is_dir('images')) {
            mkdir('images');
        }

        if ($image && $image['tmp_name']) {
            $imagePath = 'images/' . randomString(8) . '/' . $image['name'];
            mkdir(dirname($imagePath));
            move_uploaded_file($image['tmp_name'], $imagePath);
        }

        $statement = $pdo->prepare("INSERT INTO products (title, image, description, price, create_date)
                            VALUES (:title, :image, :description, :price, :date) ");
        $statement->bindValue(':title', $title);
        $statement->bindValue(':image', $imagePath);
        $statement->bindValue(':description', $description);
        $statement->bindValue(':price', $price);
        $statement->bindValue(':date', $date);
        $statement->execute();

        header('Location: index.php');
    }
}


?>


<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KyZXEAg3QhqLMpG8r+8fhAXLRk2vvoC2f3B09zVXn8CA5QIVfZOJ3BCsw2P0p/We" crossorigin="anonymous">
    <link rel="stylesheet" href="app.css">
    <title>Products CRUD</title>
</head>

<body>
    <h1>Create New Product</h1>

    <?php if (!empty($errors)) : ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error) : ?>
                <div><?php echo $error; ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Product Image</label>
            <br>
            <input type="file" name="image">
        </div>
        <div class="mb-3">
            <label class="form-label">Product Title</label>
            <input type="text" name="title" value="<?php echo $title ?>" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label">Product Description</label>
            <textarea name="description" class="form-control"><?php echo $description ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Product Price</label>
            <input type="number" name="price" value="<?php echo $price ?>" step="0.01" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>
    </form>

</body>

</html>