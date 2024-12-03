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

// Creare trigger
$triggerSQL = "
    CREATE TRIGGER trg_update_disponibil_incasari
    AFTER INSERT ON achizitii
    FOR EACH ROW
    BEGIN
        DECLARE rest DECIMAL(10, 2);
        
        -- Calcul rest de plată
        SET rest = NEW.suma_incasata - NEW.pret;

        -- Dacă suma încasată este mai mare decât prețul
        IF rest > 0 THEN
            -- Actualizare disponibil și suma încasată
            UPDATE clienti
            SET disponibil_in_cont = disponibil_in_cont + rest
            WHERE id_client = NEW.id_client;

            -- Ajustare suma încasată la prețul produsului
            UPDATE achizitii
            SET suma_incasata = NEW.pret
            WHERE id_achizitie = NEW.id_achizitie;
        END IF;
    END;
";

if ($conn->query("DROP TRIGGER IF EXISTS trg_update_disponibil_incasari") === TRUE) {
    echo "Trigger existent șters cu succes.<br>";
}

if ($conn->query($triggerSQL) === TRUE) {
    echo "Triggerul 'trg_update_disponibil_incasari' a fost creat cu succes!";
} else {
    echo "Eroare la crearea triggerului: " . $conn->error;
}

$conn->close();
?>
