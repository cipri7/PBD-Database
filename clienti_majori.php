<?php
function clientiMajori() {
    // Conexiune la baza de date
    $servername = "localhost";
    $username = "root";
    $password = "root1234";
    $dbname = "firma_distributie";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Conexiunea a eșuat: " . $conn->connect_error);
    }

    // Query pentru clienți majori fără LIMIT în subquery
    $sql = "
        SELECT c.nume, c.prenume, c.CNP, SUM(a.suma_incasata) AS suma_totala
        FROM clienti c
        JOIN achizitii a ON c.id_client = a.id_client
        WHERE 
            -- Număr minim de produse achitate integral
            (
                SELECT COUNT(*) 
                FROM achizitii ach
                WHERE ach.id_client = c.id_client AND ach.pret = ach.suma_incasata
            ) >= 4

            -- Sau valoarea totală a produselor depășește 1000 în doi ani consecutivi
            OR (
                SELECT SUM(ach.pret) 
                FROM achizitii ach
                WHERE ach.id_client = c.id_client
                  AND YEAR(ach.data_achizitie) BETWEEN (
                      SELECT MIN(YEAR(data_achizitie)) 
                      FROM achizitii
                      WHERE id_client = c.id_client
                  ) AND (
                      SELECT MIN(YEAR(data_achizitie)) + 1
                      FROM achizitii
                      WHERE id_client = c.id_client
                  )
            ) > 1000

        -- Și niciun produs achitat parțial
        AND NOT EXISTS (
            SELECT 1 
            FROM achizitii ach_partial
            WHERE ach_partial.id_client = c.id_client AND ach_partial.pret > ach_partial.suma_incasata
        )
        
        GROUP BY c.nume, c.prenume, c.CNP
        HAVING SUM(a.suma_incasata) > 0
    ";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<h2>Clienți Major</h2>";
        echo "<table border='1'>";
        echo "<tr><th>Nume</th><th>Prenume</th><th>CNP</th><th>Suma Totală Încăsată</th></tr>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['nume']) . "</td>";
            echo "<td>" . htmlspecialchars($row['prenume']) . "</td>";
            echo "<td>" . htmlspecialchars($row['CNP']) . "</td>";
            echo "<td>" . htmlspecialchars($row['suma_totala']) . "</td>";
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "Nu există clienți majori conform criteriilor.";
    }

    $conn->close();
}

// Execută funcția
clientiMajori();
?>
