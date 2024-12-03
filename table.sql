-- Comenzile pentru crearea tabelelor in MySQL --

-- Crearea bazei de date
CREATE DATABASE firma_distributie;
USE firma_distributie;

-- Crearea tabelei clienti
CREATE TABLE clienti (
    id_client INT AUTO_INCREMENT PRIMARY KEY, -- Cheie primară
    nume VARCHAR(15) NOT NULL,                -- Maxim 15 caractere
    prenume VARCHAR(20) NOT NULL,             -- Maxim 20 caractere
    CNP CHAR(13) UNIQUE NOT NULL,             -- Exact 13 caractere, unic
    adresa TEXT NOT NULL,                     -- Adresă fără limită impusă
    telefon CHAR(9) NOT NULL,                 -- Exact 9 caractere
    disponibil_in_cont DECIMAL(10, 2) UNSIGNED NOT NULL -- Valoare pozitivă cu două zecimale
);

-- Crearea tabelei achizitii
CREATE TABLE achizitii (
    id_achizitie INT AUTO_INCREMENT PRIMARY KEY, -- Cheie primară
    id_client INT NOT NULL,                      -- Referință către tabela clienti
    produs VARCHAR(50) NOT NULL,                -- Numele produsului
    data_achizitie DATE NOT NULL,               -- Data achiziției
    pret DECIMAL(10, 2) UNSIGNED NOT NULL,      -- Prețul produsului, pozitiv
    suma_incasata DECIMAL(10, 2) UNSIGNED NOT NULL, -- Suma încasată, pozitivă
    FOREIGN KEY (id_client) REFERENCES clienti(id_client) -- Constrângere de cheie străină
);


-- Popularea bazei de date Clienti
INSERT INTO clienti (nume, prenume, CNP, adresa, telefon, disponibil_in_cont)
VALUES 
('Ion', 'Adi', '1721109417596', 'Timis', '123456789', 0),
('Achim', 'Gheorghe', '1721109514891', 'Resita', '234567890', 0),
('Dima', 'Alex', '1721109514894', 'Arad', '326774434', 0),
('Duma', 'Mihai', '1721109514892', 'Deva', '576325767', 0);



-- Inserare achiziții pentru clienți
INSERT INTO achizitii (id_client, produs, data_achizitie, pret, suma_incasata)
VALUES 
-- Achiziții pentru Ion Adi
((SELECT id_client FROM clienti WHERE CNP = '1721109417596' LIMIT 1), 'Indesit', '2007-01-10', 66, 66),
((SELECT id_client FROM clienti WHERE CNP = '1721109417596' LIMIT 1), 'Sony', '2006-05-14', 7.99, 1.99),

-- Achiziții pentru Achim Gheorghe
((SELECT id_client FROM clienti WHERE CNP = '1721109514891' LIMIT 1), 'Siemens', '2003-02-01', 100, 100),

-- Achiziții pentru Dima Alex
((SELECT id_client FROM clienti WHERE CNP = '1721109514894' LIMIT 1), 'Bosch', '2005-12-07', 120, 20),

-- Achiziții pentru Duma Mihai
((SELECT id_client FROM clienti WHERE CNP = '1721109514892' LIMIT 1), 'LG', '2005-12-11', 55, 37);
