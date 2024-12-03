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

// Generarea raportului detaliat
$sql = "
    SELECT c.nume, c.prenume, c.CNP, a.produs, a.pret, (a.pret - a.suma_incasata) AS rest_de_plata, a.data_achizitie
    FROM clienti c
    JOIN achizitii a ON c.id_client = a.id_client
    ORDER BY c.nume ASC, c.prenume ASC, a.data_achizitie ASC, rest_de_plata DESC
";

$result = $conn->query($sql);

echo "<h1>Raport Detaliat: Achiziții Clienți</h1>";

if ($result->num_rows > 0) {
    echo "<table border='1'>
            <tr>
                <th>Nume</th>
                <th>Prenume</th>
                <th>CNP</th>
                <th>Produs</th>
                <th>Preț</th>
                <th>Rest de Plată</th>
                <th>Data Achiziției</th>
            </tr>";

    // Afișarea rezultatelor
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . htmlspecialchars($row['nume']) . "</td>
                <td>" . htmlspecialchars($row['prenume']) . "</td>
                <td>" . htmlspecialchars($row['CNP']) . "</td>
                <td>" . htmlspecialchars($row['produs']) . "</td>
                <td>" . number_format($row['pret'], 2) . " RON</td>
                <td>" . number_format($row['rest_de_plata'], 2) . " RON</td>
                <td>" . htmlspecialchars($row['data_achizitie']) . "</td>
              </tr>";
    }

    echo "</table>";
} else {
    echo "Nu există date disponibile pentru raportul detaliat.";
}

$conn->close();
?>
