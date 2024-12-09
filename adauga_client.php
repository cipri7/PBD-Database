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

echo "
<style>
    .error-message {
        width: 50%;
        margin: 20px auto;
        padding: 15px;
        background-color: #ffcccc;
        color: #a00;
        border: 1px solid #a00;
        border-radius: 5px;
        font-family: Arial, sans-serif;
        text-align: center;
        font-size: 1.2em;
    }
</style>";

echo "
<style>
    .succes-message {
        width: 50%;
        margin: 20px auto;
        padding: 15px;
        background-color: #ccffcc;
        color: #0a0;
        border: 1px solid #0a0;
        border-radius: 5px;
        font-family: Arial, sans-serif;
        text-align: center;
        font-size: 1.2em;
    }
</style>";

// Adăugarea unui client nou
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nume = $_POST['nume'];
    $prenume = $_POST['prenume'];
    $CNP = $_POST['CNP'];
    $adresa = $_POST['adresa'];
    $telefon = $_POST['telefon'];
    $disponibil = floatval($_POST['disponibil']);

    // Validare date
    // Verificare nume și prenume (trebuie să conțină doar litere)
    if (!ctype_alpha($nume) || !ctype_alpha($prenume)) {
        echo "
        <div class='error-message'>
            Numele și prenumele trebuie să conțină doar litere.
        </div>";
        return;
    }
    if (strlen($CNP) !== 13 || !ctype_digit($CNP)) {
        echo"
        <div class='error-message'>
            CNP-ul trebuie să aibă exact 13 cifre.
        </div>";
        return;
    }
    if (strlen($telefon) !== 9 || !ctype_digit($telefon)) {
        echo"
        <div class='error-message'>
            Numărul de telefon trebuie să aibă exact 9 cifre.
        </div>";
        return;
    }


    // Verificarea dacă CNP-ul există deja în baza de date
    $sql_check = "SELECT id_client FROM clienti WHERE CNP = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $CNP);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        echo "
        <div class='error-message'>
            Există deja un client cu CNP-ul $CNP.
        </div>";
        return;
    }

    // Inserare client în baza de date
    $sql = "INSERT INTO clienti (nume, prenume, CNP, adresa, telefon, disponibil_in_cont)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssd", $nume, $prenume, $CNP, $adresa, $telefon, $disponibil);

    if ($stmt->execute()) {
        echo "
        <div class='succes-message'>
            Clientul a fost adăugat cu succes!
        </div>";
    } else {
        echo"
        <div class='error-message'>
            Eroare la adăugarea clientului: " . $stmt->error;
        "</div>";
    }
    $stmt->close();
}

$conn->close();
?>
