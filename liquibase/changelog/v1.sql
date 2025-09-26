--liquibase formatted sql

--changeset Baptiste:1
--comment: Table Compte
CREATE TABLE Compte (
    id SERIAL PRIMARY KEY,
    identifiant VARCHAR(50) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL
);
--rollback DROP TABLE Compte;

--changeset Baptiste:2
--comment: Table Etudiant
CREATE TABLE Etudiant (
    id SERIAL PRIMARY KEY,
    compte_id INTEGER UNIQUE NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    formation VARCHAR(100) NOT NULL,
   
    FOREIGN KEY (compte_id) REFERENCES Compte(id) ON DELETE CASCADE
);
--rollback DROP TABLE Etudiant;

--changeset Baptiste:3
--comment: Table Professeur
CREATE TABLE Professeur (
    id SERIAL PRIMARY KEY,
    compte_id INTEGER UNIQUE NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
   
    FOREIGN KEY (compte_id) REFERENCES Compte(id) ON DELETE CASCADE
);
--rollback DROP TABLE Professeur;

--changeset Baptiste:4
--comment: Table Responsable_Pedagogique
CREATE TABLE Responsable_Pedagogique (
    id SERIAL PRIMARY KEY,
    compte_id INTEGER UNIQUE NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
   
    FOREIGN KEY (compte_id) REFERENCES Compte(id) ON DELETE CASCADE
);
--rollback DROP TABLE Responsable_Pedagogique;

--changeset Baptiste:5
--comment: Table Cours
CREATE TABLE Cours (
    id SERIAL PRIMARY KEY,
    professeur_id INTEGER NOT NULL,
    type VARCHAR(50) NOT NULL,
    eval BOOLEAN DEFAULT FALSE,
    date_debut TIMESTAMP NOT NULL,
    date_fin TIMESTAMP NOT NULL,
   
    FOREIGN KEY (professeur_id) REFERENCES Professeur(id) ON DELETE CASCADE,
   
    --Contrainte pour s'assurer que date_debut < date_fin
    CONSTRAINT chk_dates CHECK (date_debut < date_fin)
);
--rollback DROP TABLE Cours;

--changeset Baptiste:6
--comment: Table Ressource
CREATE TABLE Ressource (
    id SERIAL PRIMARY KEY,
    cours_id INTEGER NOT NULL,
    nom VARCHAR(255) NOT NULL,
   
    FOREIGN KEY (cours_id) REFERENCES Cours(id) ON DELETE CASCADE
);
--rollback DROP TABLE Ressource;

--changeset Baptiste:7
--comment: Table Absence
CREATE TABLE Absence (
    id SERIAL PRIMARY KEY,
    etudiant_id INTEGER NOT NULL,
    cours_id INTEGER NOT NULL,
    date_debut TIMESTAMP NOT NULL,
    date_fin TIMESTAMP NOT NULL,
    motif TEXT,
    justifie BOOLEAN DEFAULT FALSE,
   
    FOREIGN KEY (etudiant_id) REFERENCES Etudiant(id) ON DELETE CASCADE,
    FOREIGN KEY (cours_id) REFERENCES Cours(id) ON DELETE CASCADE,
   
    CONSTRAINT chk_absence_dates CHECK (date_debut < date_fin)
);
--rollback DROP TABLE Absence;

--changeset Baptiste:11
--comment: Insertion d'un compte etudiant et de son profil Etudiant
INSERT INTO Compte (identifiant, mot_de_passe)
VALUES ('dilara.simsek', 'motdepasse123');

INSERT INTO Etudiant (compte_id, nom, prenom, formation)
SELECT id, 'Simsek', 'Dilara', 'Informatique'
FROM Compte
WHERE identifiant = 'dilara.simsek';
--rollback DELETE FROM Etudiant WHERE compte_id = (SELECT id FROM Compte WHERE identifiant = 'dilara.simsek'); DELETE FROM Compte WHERE identifiant = 'dilara.simsek';

--changeset Selyane:12
--comment: Insertion d'un compte professeur et de son profil Professeur
INSERT INTO Compte (identifiant, mot_de_passe)
VALUES ('john.doe', 'securepassword'); 

INSERT INTO Professeur (compte_id, nom, prenom)
SELECT id, 'Doe', 'John'
FROM Compte
WHERE identifiant = 'john.doe';
--rollback DELETE FROM Professeur WHERE compte_id = (SELECT id FROM Compte WHERE identifiant = 'john.doe'); DELETE FROM Compte WHERE identifiant = 'john.doe';

--changeset Selyane:13
--comment: Insertion d'un compte responsable pédagogique et de son profil Responsable_Pedagogique
INSERT INTO Compte (identifiant, mot_de_passe)
VALUES ('jane.smith', 'anotherpassword');

INSERT INTO Responsable_Pedagogique (compte_id, nom, prenom)
SELECT id, 'Smith', 'Jane'
FROM Compte
WHERE identifiant = 'jane.smith';
--rollback DELETE FROM Responsable_Pedagogique WHERE compte_id = (SELECT id FROM Compte WHERE identifiant = 'jane.smith'); DELETE FROM Compte WHERE identifiant = 'jane.smith';

--changeset Selyane:14
--comment: Insertion d'un cours associé au professeur John Doe
INSERT INTO Cours (professeur_id, type, eval, date_debut, date_fin)
SELECT p.id, 'CM', FALSE, '2024-09-01 9:30:00', '2024-09-01 11:00:00'
FROM Professeur p
JOIN Compte c ON p.compte_id = c.id
WHERE c.identifiant = 'john.doe';
--rollback DELETE FROM Cours WHERE professeur_id = (SELECT p.id FROM Professeur p JOIN Compte c ON p.compte_id = c.id WHERE c.identifiant = 'john.doe');

--changeset Selyane:15
--comment: Insertion d'une ressource associée au cours créé précédemment
INSERT INTO Ressource (cours_id, nom)
SELECT c.id, 'Introduction to Programming'
FROM Cours c
JOIN Professeur p ON c.professeur_id = p.id
JOIN Compte cp ON p.compte_id = cp.id
WHERE cp.identifiant = 'john.doe' AND c.date_debut = '2024-09-01 9:30:00';
--rollback DELETE FROM Ressource WHERE cours_id = (SELECT c.id FROM Cours c JOIN Professeur p ON c.professeur_id = p.id JOIN Compte cp ON p.compte_id = cp.id WHERE cp.identifiant = 'john.doe' AND c.date_debut = '2024-09-01 9:30:00');

--changeset Selyane:16
--comment: Insertion d'une absence pour l'étudiant Dilara Simsek pour le cours créé précédemment
INSERT INTO Absence (etudiant_id, cours_id, date_debut, date_fin, motif, justifie)
SELECT e.id, c.id, '2024-09-01 9:30:00', '2024-09-01 11:00:00', 'Maladie', FALSE
FROM Etudiant e
JOIN Compte ce ON e.compte_id = ce.id
JOIN Cours c ON c.date_debut = '2024-09-01 9:30:00'
JOIN Professeur p ON c.professeur_id = p.id
JOIN Compte cp ON p.compte_id = cp.id
WHERE ce.identifiant = 'dilara.simsek' AND cp.identifiant = 'john.doe';
--rollback DELETE FROM Absence WHERE etudiant_id = (SELECT e.id FROM Etudiant e JOIN Compte ce ON e.compte_id = ce.id WHERE ce.identifiant = 'dilara.simsek') AND cours_id = (SELECT c.id FROM Cours c JOIN Professeur p ON c.professeur_id = p.id JOIN Compte cp ON p.compte_id = cp.id WHERE cp.identifiant = 'john.doe' AND c.date_debut = '2024-09-01 9:30:00');
-- End of changelog (for now)
