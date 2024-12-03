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

// Procedura de vânzare
function vanzareProdus($CNP, $produs, $data, $pret, $sumaIncasata) {
    global $conn;

    // Validare valori pozitive pentru preț și sumă încasată
    if ($pret < 0 || $sumaIncasata < 0) {
        die("Valori invalide: prețul și suma încasată trebuie să fie pozitive.");
    }

    // 1. Găsirea clientului după CNP
    $sql = "SELECT id_client, disponibil_in_cont FROM clienti WHERE CNP = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $CNP);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "Clientul cu CNP-ul $CNP nu a fost găsit.";
        return;
    }

    $client = $result->fetch_assoc();
    $id_client = $client['id_client'];
    $disponibil = $client['disponibil_in_cont'];

    // 2. Calcularea logicii de plată
    $sumaRamasa = $pret - $sumaIncasata; // Ce rămâne de achitat după suma încasată
    $nouDisponibil = $disponibil;       // Disponibilul inițial al clientului

    if ($sumaRamasa <= 0) {
        // Dacă suma încasată este mai mare decât prețul, actualizăm disponibilul clientului
        $nouDisponibil += abs($sumaRamasa);
        $sumaRamasa = 0; // Produsul este complet plătit
    } else {
        // Dacă suma încasată este mai mică decât prețul, scădem din disponibil
        if ($disponibil >= $sumaRamasa) {
            $nouDisponibil -= $sumaRamasa;
            $sumaRamasa = 0; // Produsul este complet plătit
        } else {
            echo "Disponibilul clientului este insuficient pentru această achiziție.";
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

    echo "Produsul $produs a fost vândut cu succes!";
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
