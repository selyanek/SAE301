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