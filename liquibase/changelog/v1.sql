--liquibase formatted sql

--changeset Baptiste:1
--comment: Table Compte
CREATE TABLE Compte (
    idCompte TEXT PRIMARY KEY,
    mot_de_passe TEXT NOT NULL,
    nom TEXT NOT NULL,
    prenom TEXT NOT NULL,
    fonction TEXT NOT NULL
);
--rollback DROP TABLE Compte;

--changeset Baptiste:2
--comment: Table Etudiant
CREATE TABLE Etudiant (
    idEtudiant TEXT PRIMARY KEY,
    formation TEXT,
    FOREIGN KEY (idEtudiant) REFERENCES Compte(idCompte) ON DELETE CASCADE
);
--rollback DROP TABLE Etudiant;

--changeset Baptiste:3
--comment: Table Professeur
CREATE TABLE Professeur (
    idProfesseur TEXT PRIMARY KEY,
    FOREIGN KEY (idProfesseur) REFERENCES Compte(idCompte) ON DELETE CASCADE
);
--rollback DROP TABLE Professeur;

--changeset Baptiste:4
--comment: Table Responsable_Pedagogique
CREATE TABLE Responsable_Pedagogique (
    idResponsablePedagogique TEXT PRIMARY KEY,
    FOREIGN KEY (idResponsablePedagogique) REFERENCES Compte(idCompte) ON DELETE CASCADE
);
--rollback DROP TABLE Responsable_Pedagogique;

--changeset Baptiste:5
--comment: Table Ressource
CREATE TABLE Ressource (
    idRessource SERIAL PRIMARY KEY,
    nom TEXT NOT NULL
);
--rollback DROP TABLE Ressource;

--changeset Baptiste:6
--comment: Table Cours
CREATE TABLE Cours (
    idCours SERIAL PRIMARY KEY,
    type TEXT NOT NULL,
    seuil BOOLEAN NOT NULL,
    date_debut TIMESTAMP NOT NULL,
    date_fin TIMESTAMP NOT NULL,
    idRessource INTEGER NOT NULL,
    idProfesseur TEXT NOT NULL,
    idResponsablePedagogique TEXT NOT NULL,
    FOREIGN KEY (idRessource) REFERENCES Ressource(idRessource) ON DELETE CASCADE,
    FOREIGN KEY (idProfesseur) REFERENCES Professeur(idProfesseur) ON DELETE CASCADE,
    FOREIGN KEY (idResponsablePedagogique) REFERENCES Responsable_Pedagogique(idResponsablePedagogique) ON DELETE CASCADE
);
--rollback DROP TABLE Cours;

--changeset Baptiste:7
--comment: Table Absence
CREATE TABLE Absence (
    idAbsence SERIAL PRIMARY KEY,
    date_debut TIMESTAMP NOT NULL,
    date_fin TIMESTAMP NOT NULL,
    motif TEXT,
    idEtudiant TEXT NOT NULL,
    justifie BOOLEAN NOT NULL DEFAULT FALSE,
    idCours INTEGER NOT NULL,
    FOREIGN KEY (idEtudiant) REFERENCES Etudiant(idEtudiant) ON DELETE CASCADE,
    FOREIGN KEY (idCours) REFERENCES Cours(idCours) ON DELETE CASCADE
);
--rollback DROP TABLE Absence;

--changeset Baptiste:8
--comment: Insertion d'un compte etudiant et de son profil Etudiant
INSERT INTO Compte (idCompte, mot_de_passe, nom, prenom, fonction)
VALUES ('dilara.simsek', 'motdepasse123', 'Simsek', 'Dilara', 'etudiante');

INSERT INTO Etudiant (idEtudiant, formation)
VALUES ('dilara.simsek', 'Informatique');
--rollback DELETE FROM Etudiant WHERE idEtudiant = 'dilara.simsek'; DELETE FROM Compte WHERE idCompte = 'dilara.simsek';

--changeset Selyane:9
--comment: Insertion d'un compte professeur et de son profil Professeur
INSERT INTO Compte (idCompte, mot_de_passe, nom, prenom, fonction)
VALUES ('john.doe', 'x', 'Doe', 'John', 'professeur'); 

INSERT INTO Professeur (idProfesseur)
VALUES ('john.doe');
--rollback DELETE FROM Professeur WHERE idProfesseur = 'john.doe'; DELETE FROM Compte WHERE idCompte = 'john.doe';

--changeset Selyane:10
--comment: Insertion d'un compte responsable pédagogique et de son profil Responsable_Pedagogique
INSERT INTO Compte (idCompte, mot_de_passe, nom, prenom, fonction)
VALUES ('jane.smith', 'anotherpassword', 'Smith', 'Jane', 'responsable_pedagogique');

INSERT INTO Responsable_Pedagogique (idResponsablePedagogique)
VALUES ('jane.smith');
--rollback DELETE FROM Responsable_Pedagogique WHERE idResponsablePedagogique = 'jane.smith'; DELETE FROM Compte WHERE idCompte = 'jane.smith';

--changeset Selyane:11
--comment: Insertion d'une ressource
INSERT INTO Ressource (nom)
VALUES ('Introduction to Programming');
--rollback DELETE FROM Ressource WHERE nom = 'Introduction to Programming';

--changeset Selyane:12
--comment: Insertion d'un cours associé au professeur John Doe
INSERT INTO Cours (type, seuil, date_debut, date_fin, idRessource, idProfesseur, idResponsablePedagogique)
VALUES ('CM', FALSE, '2024-09-01 09:30:00', '2024-09-01 11:00:00', 
        (SELECT idRessource FROM Ressource WHERE nom = 'Introduction to Programming'),
        'john.doe', 
        'jane.smith');
--rollback DELETE FROM Cours WHERE idProfesseur = 'john.doe' AND date_debut = '2024-09-01 09:30:00';

--changeset Selyane:13
--comment: Insertion d'une absence pour l'étudiant Dilara Simsek
INSERT INTO Absence (date_debut, date_fin, motif, justifie, idEtudiant, idCours)
VALUES ('2024-09-01 09:30:00', '2024-09-01 11:00:00', 'Maladie', FALSE,
        'dilara.simsek',
        (SELECT idCours FROM Cours WHERE idProfesseur = 'john.doe' AND date_debut = '2024-09-01 09:30:00'));
--rollback DELETE FROM Absence WHERE idEtudiant = 'dilara.simsek' AND idCours = (SELECT idCours FROM Cours WHERE idProfesseur = 'john.doe' AND date_debut = '2024-09-01 09:30:00');

-- End of changelog