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

        // Găsește id_client pe baza CNP-ului
        $sql_client = "SELECT id_client FROM clienti WHERE CNP = ?";
        $stmt_client = $conn->prepare($sql_client);
        $stmt_client->bind_param("s", $CNP);
        $stmt_client->execute();
        $result_client = $stmt_client->get_result();

        if ($result_client->num_rows == 0) {    
            echo "
                <div class='error-message'>
                    Clientul cu CNP-ul $CNP nu a fost găsit.
                </div>
            ";
            return;
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
            echo "
                <div class='error-message'>
                    Produsul $produs nu a fost găsit pentru clientul cu CNP-ul $CNP.
                </div>
            ";
            return;
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
    // Acum afișăm restul doar dacă produsul a fost găsit și restul de plată există
    if ($rest !== null) {
    echo "
        <div class='succes-message'>
            Rest de plată pentru produsul $produs al clientului cu CNP-ul $CNP: $rest RON.
        </div>";
    } else {
        echo "
            <div class='error-message'>
                Formular invalid.
            </div>";
    }
}
?>
