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

// Generarea raportului
$sql = "
    SELECT c.nume, c.prenume, c.CNP, a.produs
    FROM clienti c
    JOIN achizitii a ON c.id_client = a.id_client
    WHERE a.pret = a.suma_incasata
";

$result = $conn->query($sql);

echo "<h1>Raport: Produse Achitate Integral</h1>";

if ($result->num_rows > 0) {
    echo "<table border='1'>
            <tr>
                <th>Nume</th>
                <th>Prenume</th>
                <th>CNP</th>
                <th>Produs</th>
            </tr>";

    // Afișarea rezultatelor
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . htmlspecialchars($row['nume']) . "</td>
                <td>" . htmlspecialchars($row['prenume']) . "</td>
                <td>" . htmlspecialchars($row['CNP']) . "</td>
                <td>" . htmlspecialchars($row['produs']) . "</td>
              </tr>";
    }

    echo "</table>";
} else {
    echo "Nu există produse achitate integral.";
}

$conn->close();
?>
