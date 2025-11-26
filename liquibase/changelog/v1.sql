--liquibase formatted sql

--changeset Baptiste:1
--comment: Table Compte
CREATE TABLE Compte
(
    idCompte          SERIAL PRIMARY KEY,
    identifiantCompte TEXT NOT NULL UNIQUE,
    mot_de_passe      TEXT NOT NULL,
    nom               TEXT NOT NULL,
    prenom            TEXT NOT NULL,
    fonction          TEXT NOT NULL
);
--rollback DROP TABLE Compte;

--changeset Baptiste:2
--comment: Table Etudiant
CREATE TABLE Etudiant
(
    idEtudiant     INT PRIMARY KEY,
    identifiantEtu TEXT NOT NULL,
    formation      TEXT NOT NULL,
    FOREIGN KEY (idEtudiant) REFERENCES Compte (idCompte) ON DELETE CASCADE
);
--rollback DROP TABLE Etudiant;

--changeset Baptiste:3
--comment: Table Professeur
CREATE TABLE Professeur
(
    idProfesseur    INT PRIMARY KEY,
    identifiantProf TEXT NOT NULL,
    FOREIGN KEY (idProfesseur) REFERENCES Compte (idCompte) ON DELETE CASCADE
);
--rollback DROP TABLE Professeur;

--changeset Baptiste:4
--comment: Table Responsable_Pedagogique
CREATE TABLE Responsable_Pedagogique
(
    idResponsablePedagogique INT PRIMARY KEY,
    identifiantRp            TEXT NOT NULL,
    FOREIGN KEY (idResponsablePedagogique) REFERENCES Compte (idCompte) ON DELETE CASCADE
);
--rollback DROP TABLE Responsable_Pedagogique;

--changeset Baptiste:5
--comment: Table Ressource
CREATE TABLE Ressource
(
    idRessource SERIAL PRIMARY KEY,
    nom         TEXT NOT NULL UNIQUE
);
--rollback DROP TABLE Ressource;

--changeset Baptiste:6
--comment: Table Cours
CREATE TABLE Cours
(
    idCours                  SERIAL PRIMARY KEY,
    idRessource              INT       NOT NULL,
    idProfesseur             INT       NOT NULL,
    idResponsablePedagogique INT       NOT NULL,
    type                     TEXT      NOT NULL,
    evaluation               BOOLEAN   NOT NULL,
    date_debut               TIMESTAMP NOT NULL,
    date_fin                 TIMESTAMP NOT NULL,
    FOREIGN KEY (idRessource) REFERENCES Ressource (idRessource) ON DELETE CASCADE,
    FOREIGN KEY (idProfesseur) REFERENCES Professeur (idProfesseur) ON DELETE CASCADE,
    FOREIGN KEY (idResponsablePedagogique) REFERENCES Responsable_Pedagogique (idResponsablePedagogique) ON DELETE CASCADE
);
--rollback DROP TABLE Cours;

--changeset Baptiste:7
--comment: Table Absence
CREATE TABLE Absence
(
    idAbsence       SERIAL PRIMARY KEY,
    idCours         INT       NOT NULL,
    idEtudiant      INT       NOT NULL,
    date_debut      TIMESTAMP NOT NULL,
    date_fin        TIMESTAMP NOT NULL,
    motif           TEXT,
    justifie        BOOLEAN   NOT NULL DEFAULT FALSE,
    uriJustificatif TEXT      NULL,
    FOREIGN KEY (idEtudiant) REFERENCES Etudiant (idEtudiant) ON DELETE CASCADE,
    FOREIGN KEY (idCours) REFERENCES Cours (idCours) ON DELETE CASCADE
);
--rollback DROP TABLE Absence;

--changeset Baptiste:8
--comment: Insertion d'un compte etudiant et de son profil Etudiant
INSERT INTO Compte (identifiantCompte, mot_de_passe, nom, prenom, fonction)
VALUES ('dilara.simsek', 'motdepasse123', 'Simsek', 'Dilara', 'etudiant');

INSERT INTO Etudiant (idEtudiant, identifiantEtu, formation)
VALUES (
           (SELECT idCompte FROM Compte WHERE identifiantCompte = 'dilara.simsek'),
           'dilara.simsek',
           'Informatique'
       );
--rollback DELETE FROM Etudiant WHERE identifiantEtu = 'dilara.simsek'; DELETE FROM Compte WHERE identifiantCompte = 'dilara.simsek';

--changeset Selyane:9
--comment: Insertion d'un compte professeur et de son profil Professeur
INSERT INTO Compte (identifiantCompte, mot_de_passe, nom, prenom, fonction)
VALUES ('john.doe', 'x', 'Doe', 'John', 'professeur');

INSERT INTO Professeur (idProfesseur, identifiantProf)
VALUES (
           (SELECT idCompte FROM Compte WHERE identifiantCompte = 'john.doe'),
           'john.doe'
       );
--rollback DELETE FROM Professeur WHERE identifiantProf = 'john.doe'; DELETE FROM Compte WHERE identifiantCompte = 'john.doe';

--changeset Selyane:10
--comment: Insertion d'un compte responsable pédagogique et de son profil Responsable_Pedagogique
INSERT INTO Compte (identifiantCompte, mot_de_passe, nom, prenom, fonction)
VALUES ('christelle.roze', 'anotherpassword', 'Roze', 'Christelle', 'responsable_pedagogique');

INSERT INTO Responsable_Pedagogique (idResponsablePedagogique, identifiantRp)
VALUES (
           (SELECT idCompte FROM Compte WHERE identifiantCompte = 'christelle.roze'),
           'christelle.roze'
       );
--rollback DELETE FROM Responsable_Pedagogique WHERE identifiantRp = 'christelle.roze'; DELETE FROM Compte WHERE identifiantCompte = 'christelle.roze';

--changeset Selyane:11
--comment: Insertion d'une ressource
INSERT INTO Ressource (nom)
VALUES ('Introduction to Programming');
--rollback DELETE FROM Ressource WHERE nom = 'Introduction to Programming';

--changeset Selyane:12
--comment: Insertion d'un cours associé au professeur John Doe
INSERT INTO Cours (type, evaluation, date_debut, date_fin, idRessource, idProfesseur, idResponsablePedagogique)
VALUES (
           'CM',
           FALSE,
           '2024-09-01 09:30:00',
           '2024-09-01 11:00:00',
           (SELECT idRessource FROM Ressource WHERE nom = 'Introduction to Programming'),
           (SELECT idProfesseur FROM Professeur WHERE identifiantProf = 'john.doe'),
           (SELECT idResponsablePedagogique FROM Responsable_Pedagogique WHERE identifiantRp = 'jane.smith')
       );
--rollback DELETE FROM Cours WHERE date_debut = '2024-09-01 09:30:00';

--changeset Selyane:13
--comment: Insertion d'une absence pour l'étudiant Dilara Simsek
INSERT INTO Absence (date_debut, date_fin, motif, justifie, idEtudiant, idCours)
VALUES (
           '2024-09-01 09:30:00',
           '2024-09-01 11:00:00',
           'Maladie',
           FALSE,
           (SELECT idEtudiant FROM Etudiant WHERE identifiantEtu = 'dilara.simsek'),
           (SELECT idCours FROM Cours WHERE date_debut = '2024-09-01 09:30:00')
       );
--rollback DELETE FROM Absence WHERE idEtudiant = (SELECT idEtudiant FROM Etudiant WHERE identifiantEtu = 'dilara.simsek');

--changeset Roman:14
--comment: Insertion d'un autre compte etudiant et de son profil Etudiant
INSERT INTO Compte (identifiantCompte, mot_de_passe, nom, prenom, fonction)
VALUES ('alice.martin', 'securepass456', 'Martin', 'Alice', 'etudiant');

INSERT INTO Etudiant (idEtudiant, identifiantEtu, formation)
VALUES (
           (SELECT idCompte FROM Compte WHERE identifiantCompte = 'alice.martin'),
           'alice.martin',
           'Mathematics'
       );
--rollback DELETE FROM Etudiant WHERE identifiantEtu = 'alice.martin'; DELETE FROM Compte WHERE identifiantCompte = 'alice.martin';

-- End of changelog
