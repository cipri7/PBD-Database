<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $CNP = $_POST['cnp'];
    $produs = $_POST['produs'];

    function calculeazaRestDePlata($CNP, $produs) {
        // Conexiune la baza de date
        $servername = "localhost";
        $username = "root";
        $password = "root1234";
        $dbname = "firma_distributie";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Conexiunea a eșuat: " . $conn->connect_error);
        }

        // Găsește id_client pe baza CNP-ului
        $sql_client = "SELECT id_client FROM clienti WHERE CNP = ?";
        $stmt_client = $conn->prepare($sql_client);
        $stmt_client->bind_param("s", $CNP);
        $stmt_client->execute();
        $result_client = $stmt_client->get_result();

        if ($result_client->num_rows == 0) {
            die("Clientul cu CNP-ul $CNP nu există.");
        }

        $client = $result_client->fetch_assoc();
        $id_client = $client['id_client'];

        // Găsește achiziția pe baza id_client și numelui produsului
        $sql_produs = "
            SELECT pret, suma_incasata 
            FROM achizitii 
            WHERE id_client = ? AND produs = ?
            ORDER BY data_achizitie DESC
            LIMIT 1";
        $stmt_produs = $conn->prepare($sql_produs);
        $stmt_produs->bind_param("is", $id_client, $produs);
        $stmt_produs->execute();
        $result_produs = $stmt_produs->get_result();

        if ($result_produs->num_rows == 0) {
            die("Produsul $produs nu a fost găsit pentru clientul cu CNP-ul $CNP.");
        }

        $achizitie = $result_produs->fetch_assoc();
        $pret = $achizitie['pret'];
        $suma_incasata = $achizitie['suma_incasata'];

        // Calculare rest de plată
        $rest_de_plata = max($pret - $suma_incasata, 0);

        $stmt_client->close();
        $stmt_produs->close();
        $conn->close();

        return $rest_de_plata;
    }

    $rest = calculeazaRestDePlata($CNP, $produs);
    echo "Restul de plată pentru produsul $produs și clientul cu CNP-ul $CNP este: $rest lei.";
} else {
    echo "Formular invalid!";
}
?>
