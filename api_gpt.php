<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $input = $_POST['mensagem'];

    // Now you can process the data, e.g., save to a database, send an email, etc.
    echo "$input: " . htmlspecialchars($input);
}
?>