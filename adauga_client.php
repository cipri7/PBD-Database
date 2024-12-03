<?php
// Conexiune la baza de date
$servername = "localhost";
$username = "root";
$password = "root1234";
$dbname = "firma_distributie";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexiunea a eșuat: " . $conn->connect_error);
}

// Adăugarea unui client nou
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nume = $_POST['nume'];
    $prenume = $_POST['prenume'];
    $CNP = $_POST['CNP'];
    $adresa = $_POST['adresa'];
    $telefon = $_POST['telefon'];
    $disponibil = floatval($_POST['disponibil']);

    // Validare date
    if (strlen($CNP) !== 13 || !ctype_digit($CNP)) {
        die("CNP-ul trebuie să aibă exact 13 cifre.");
    }
    if (strlen($telefon) !== 9 || !ctype_digit($telefon)) {
        die("Telefonul trebuie să aibă exact 9 cifre.");
    }

    // Inserare client în baza de date
    $sql = "INSERT INTO clienti (nume, prenume, CNP, adresa, telefon, disponibil_in_cont)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssd", $nume, $prenume, $CNP, $adresa, $telefon, $disponibil);

    if ($stmt->execute()) {
        echo "Clientul a fost adăugat cu succes!";
    } else {
        echo "Eroare la adăugarea clientului: " . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>
