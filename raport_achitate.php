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
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f4f4f9;
    }
    h1 {
        text-align: center;
        color: #4CAF50;
        margin-top: 20px;
        font-size: 2.5em;
        font-weight: bold;
    }
    table {
        width: 80%;
        margin: 20px auto;
        border-collapse: collapse;
        font-family: Arial, sans-serif;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 10px;
        text-align: center;
    }
    th {
        background-color: #4CAF50;
        color: white;
    }
    tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    tr:hover {
        background-color: #f1f1f1;
    }
</style>
";

echo "<h1>Raport: Produse Achitate Integral</h1>";

if ($result->num_rows > 0) {
    
    echo "<table>
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
    echo "
    <div class='error-message'>
        Nu există produse achitate integral.
    </div>";
}

$conn->close();
?>
