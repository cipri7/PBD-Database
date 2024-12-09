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
        echo "<h1>Clienți Major</h1>";
        echo "<table>";
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
        echo "
            <div class='error-message'>
                Nu există clienți majori.
            </div>";
    }

    $conn->close();
}

// Execută funcția
clientiMajori();
?>
