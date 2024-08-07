<?php
header("Content-Type: application/json");

$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "product_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
$input = json_decode(file_get_contents('php://input'), true);

$id = null;
if (isset($request[1])) {
    $id = intval($request[1]);
}

switch ($method) {
    case 'GET':
        if ($id) {
            getProduct($conn, $id);
        } else {
            getProducts($conn);
        }
        break;
    case 'POST':
        addProduct($conn, $input);
        break;
    case 'PUT':
        if ($id) {
            updateProduct($conn, $id, $input);
        } else {
            echo json_encode(["error" => "ID is required for updating a product"]);
        }
        break;
    case 'DELETE':
        if ($id) {
            deleteProduct($conn, $id);
        } else {
            echo json_encode(["error" => "ID is required for deleting a product"]);
        }
        break;
    default:
        echo json_encode(["error" => "Invalid request method"]);
        break;
}

$conn->close();

function getProducts($conn) {
    $sql = "SELECT * FROM products";
    $result = $conn->query($sql);

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    echo json_encode($products);
}

function getProduct($conn, $id) {
    $sql = "SELECT * FROM products WHERE id=$id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode(["error" => "Product not found"]);
    }
}

function addProduct($conn, $input) {
    $name = $input['name'];
    $description = $input['description'];
    $price = $input['price'];

    $sql = "INSERT INTO products (name, description, price) VALUES ('$name', '$description', $price)";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(["success" => "New product created successfully"]);
    } else {
        echo json_encode(["error" => "Error: " . $sql . "<br>" . $conn->error]);
    }
}

function updateProduct($conn, $id, $input) {
    $name = $input['name'];
    $description = $input['description'];
    $price = $input['price'];

    $sql = "UPDATE products SET name='$name', description='$description', price=$price WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(["success" => "Product updated successfully"]);
    } else {
        echo json_encode(["error" => "Error: " . $sql . "<br>" . $conn->error]);
    }
}

function deleteProduct($conn, $id) {
    $sql = "DELETE FROM products WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(["success" => "Product deleted successfully"]);
    } else {
        echo json_encode(["error" => "Error: " . $sql . "<br>" . $conn->error]);
    }
}
?>
