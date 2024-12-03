<?php
function clientCuCeleMaiMulteProduseNeplatite() {
    // Conexiune la baza de date
    $servername = "localhost";
    $username = "root";
    $password = "root1234";
    $dbname = "firma_distributie";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Conexiunea a eșuat: " . $conn->connect_error);
    }

    // Interogare pentru a selecta clientul cu cele mai multe produse neplătite integral
    $sql = "
        SELECT 
            c.nume, c.prenume, c.CNP, 
            COUNT(a.id_achizitie) AS numar_produse, 
            SUM(a.suma_incasata) / SUM(a.pret) * 100 AS rata_achitare
        FROM clienti c
        JOIN achizitii a ON c.id_client = a.id_client
        WHERE a.suma_incasata < a.pret
        GROUP BY c.id_client
        ORDER BY numar_produse DESC
        LIMIT 1
    ";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "<h2>Clientul cu cele mai multe produse neplătite integral</h2>";
        echo "<table border='1'>";
        echo "<tr><th>Nume</th><th>Prenume</th><th>CNP</th><th>Număr Produse</th><th>Rata Achitare (%)</th></tr>";
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['nume']) . "</td>";
        echo "<td>" . htmlspecialchars($row['prenume']) . "</td>";
        echo "<td>" . htmlspecialchars($row['CNP']) . "</td>";
        echo "<td>" . htmlspecialchars($row['numar_produse']) . "</td>";
        echo "<td>" . number_format($row['rata_achitare'], 2) . "%</td>";
        echo "</tr>";
        echo "</table>";
    } else {
        echo "Nu există niciun client cu produse neplătite integral.";
    }

    $conn->close();
}

// Execută funcția
clientCuCeleMaiMulteProduseNeplatite();
?>
