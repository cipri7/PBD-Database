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

// Procedura de vânzare
function vanzareProdus($CNP, $produs, $data, $pret, $sumaIncasata) {
    global $conn;

    // Validare valori pozitive pentru preț și sumă încasată
    if ($pret < 0 || $sumaIncasata < 0) {
    echo"
    <div class='error-message'>
        Valori invalide: prețul și suma încasată trebuie să fie pozitive.
    </div>
    ";
    exit; // Încheie scriptul elegant
    }

    // 1. Găsirea clientului după CNP
    $sql = "SELECT id_client, disponibil_in_cont FROM clienti WHERE CNP = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $CNP);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "
        <div class='error-message'>
            Clientul cu CNP-ul $CNP nu a fost găsit.
        </div>";
        return;
    }

    $client = $result->fetch_assoc();
    $id_client = $client['id_client'];
    $disponibil = $client['disponibil_in_cont'];

    // 2. Calcularea logicii de plată
    $sumaRamasa = $pret - $sumaIncasata; // Ce rămâne de achitat după suma încasată
    $nouDisponibil = $disponibil;       // Disponibilul inițial al clientului

    // Verificăm dacă suma încasată este mai mare decât prețul produsului
    if ($sumaIncasata > $pret) {
        echo "
        <div class='error-message'>
            Suma încasată nu poate fi mai mare decât prețul produsului.
        </div>";
        return;
    }

    if ($sumaRamasa <= 0) {
            // Dacă suma încasată este mai mare sau egală cu prețul, verificăm dacă există suficient disponibil
        if ($disponibil < $pret) {
            echo "
            <div class='error-message'>
                Disponibilul clientului este insuficient pentru achitarea produsului integral.
            </div>";
            return;
        }
            // Dacă suma încasată este mai mare decât prețul, actualizăm disponibilul clientului
            //$nouDisponibil += abs($sumaRamasa);
            $nouDisponibil -= $pret;
            $sumaRamasa = 0; // Produsul este complet plătit
        } else {
        // Dacă suma încasată este mai mică decât prețul, scădem din disponibil
        if ($disponibil >= $sumaRamasa) {
            $nouDisponibil -= $sumaIncasata;
            $sumaRamasa = 0; // Produsul este complet plătit
        } else {
            echo "
            <div class='error-message'>
                Disponibilul clientului este insuficient pentru această achiziție.
            </div>";
            return;
        }
    }

    // 3. Actualizarea disponibilului clientului
    $updateClient = "UPDATE clienti SET disponibil_in_cont = ? WHERE id_client = ?";
    $stmt = $conn->prepare($updateClient);
    $stmt->bind_param("di", $nouDisponibil, $id_client);
    $stmt->execute();

    // 4. Înregistrarea achiziției în tabela `achizitii`
    $insertAchizitie = "INSERT INTO achizitii (id_client, produs, data_achizitie, pret, suma_incasata)
                        VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertAchizitie);
    $stmt->bind_param("issdd", $id_client, $produs, $data, $pret, $sumaIncasata);
    $stmt->execute();

    echo "
    <div class='succes-message'>
        Produsul $produs a fost vândut cu succes!
    </div>";
}

// Apelare funcție cu date din POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $CNP = $_POST['CNP'];
    $produs = $_POST['produs'];
    $data = $_POST['data'];
    $pret = floatval($_POST['pret']);
    $sumaIncasata = floatval($_POST['suma_incasata']);

    vanzareProdus($CNP, $produs, $data, $pret, $sumaIncasata);
}

$conn->close();
?>
