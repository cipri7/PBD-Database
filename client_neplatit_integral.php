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
                color: #FF6666;
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
                background-color: #FF6666;
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
        echo "<h1>Clientul cu cele mai multe produse neplătite integral</h1>";
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
        echo "
            <div class='error-message'>
                Nu există clienți cu produse neplătite integral.
            </div>
        ";
    }

    $conn->close();
}

// Execută funcția
clientCuCeleMaiMulteProduseNeplatite();
?>
