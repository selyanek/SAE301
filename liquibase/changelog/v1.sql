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
    CHECK ( date_fin > date_debut ),
    FOREIGN KEY (idEtudiant) REFERENCES Etudiant (idEtudiant) ON DELETE CASCADE,
    FOREIGN KEY (idCours) REFERENCES Cours (idCours) ON DELETE CASCADE
);
--rollback DROP TABLE Absence;

--changeset Baptiste:8
--comment: Insertion d'un compte etudiant et de son profil Etudiant
INSERT INTO Compte (identifiantCompte, mot_de_passe, nom, prenom, fonction)
VALUES ('dilara.simsek', 'motdepasse123', 'Simsek', 'Dilara', 'etudiant');

INSERT INTO Etudiant (idEtudiant, identifiantEtu, formation)
VALUES ((SELECT idCompte FROM Compte WHERE identifiantCompte = 'dilara.simsek'),
        'dilara.simsek',
        'Informatique');
--rollback DELETE FROM Etudiant WHERE identifiantEtu = 'dilara.simsek'; DELETE FROM Compte WHERE identifiantCompte = 'dilara.simsek';

--changeset Selyane:9
--comment: Insertion d'un compte professeur et de son profil Professeur
INSERT INTO Compte (identifiantCompte, mot_de_passe, nom, prenom, fonction)
VALUES ('john.doe', 'x', 'Doe', 'John', 'professeur');

INSERT INTO Professeur (idProfesseur, identifiantProf)
VALUES ((SELECT idCompte FROM Compte WHERE identifiantCompte = 'john.doe'),
        'john.doe');
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
           (SELECT idResponsablePedagogique FROM Responsable_Pedagogique WHERE identifiantRp = 'christelle.roze')
       );
--rollback DELETE FROM Cours WHERE date_debut = '2024-09-01 09:30:00';

--changeset Selyane:13
--comment: Insertion d'une absence pour l'étudiant Dilara Simsek
INSERT INTO Absence (date_debut, date_fin, motif, justifie, idEtudiant, idCours)
VALUES ('2024-09-01 09:30:00',
        '2024-09-01 11:00:00',
        'Maladie',
        FALSE,
        (SELECT idEtudiant FROM Etudiant WHERE identifiantEtu = 'dilara.simsek'),
        (SELECT idCours FROM Cours WHERE date_debut = '2024-09-01 09:30:00'));
--rollback DELETE FROM Absence WHERE idEtudiant = (SELECT idEtudiant FROM Etudiant WHERE identifiantEtu = 'dilara.simsek');

--changeset Roman:14
--comment: Insertion d'un autre compte etudiant et de son profil Etudiant
INSERT INTO Compte (identifiantCompte, mot_de_passe, nom, prenom, fonction)
VALUES ('alice.martin', 'securepass456', 'Martin', 'Alice', 'etudiant');

INSERT INTO Etudiant (idEtudiant, identifiantEtu, formation)
VALUES ((SELECT idCompte FROM Compte WHERE identifiantCompte = 'alice.martin'),
        'alice.martin',
        'Mathematics');
--rollback DELETE FROM Etudiant WHERE identifiantEtu = 'alice.martin'; DELETE FROM Compte WHERE identifiantCompte = 'alice.martin';

--changeset Roman:15
--comment: Ajouter le champ raison_refus à la table Absence
ALTER TABLE Absence 
ADD COLUMN raison_refus TEXT;
--rollback ALTER TABLE Absence DROP COLUMN raison_refus;

--changeset Roman:16
--comment: Création de la table Secretaire
CREATE TABLE Secretaire
(
    idSecretaire     INT PRIMARY KEY,
    identifiantSec   TEXT NOT NULL,
    FOREIGN KEY (idSecretaire) REFERENCES Compte (idCompte) ON DELETE CASCADE
);
--rollback DROP TABLE Secretaire;

--changeset Roman:17
--comment: Insertion d'un compte secrétaire
INSERT INTO Compte (identifiantCompte, mot_de_passe, nom, prenom, fonction)
VALUES ('delphine.milice', 'motdepasse123', 'Milice', 'Delphine', 'secretaire');

INSERT INTO Secretaire (idSecretaire, identifiantSec)
VALUES ((SELECT idCompte FROM Compte WHERE identifiantCompte = 'delphine.milice'),'delphine.milice');
--rollback DELETE FROM Secretaire WHERE identifiantSec = 'delphine.milice'; DELETE FROM Compte WHERE identifiantCompte = 'delphine.milice';

--changeset Roman:18
--comment: Modifier la colonne justifie pour accepter NULL (absence en attente)
ALTER TABLE Absence 
ALTER COLUMN justifie DROP NOT NULL;

ALTER TABLE Absence 
ALTER COLUMN justifie DROP DEFAULT;
--rollback ALTER TABLE Absence ALTER COLUMN justifie SET NOT NULL; ALTER TABLE Absence ALTER COLUMN justifie SET DEFAULT FALSE;

--changeset Roman:19
--comment: Insertion d'un compte etudiant et de son profil Etudiant pour Oscar Maesse
INSERT INTO Compte (identifiantCompte, mot_de_passe, nom, prenom, fonction)
VALUES ('oscar.maesse', 'motdepasse123', 'Maesse', 'Oscar', 'etudiant');

INSERT INTO Etudiant (idEtudiant, identifiantEtu, formation)
VALUES ((SELECT idCompte FROM Compte WHERE identifiantCompte = 'oscar.maesse'),
        'oscar.maesse',
        'Informatique');
--rollback DELETE FROM Etudiant WHERE identifiantEtu = 'oscar.maesse'; DELETE FROM Compte WHERE identifiantCompte = 'oscar.maesse';

--changeset Roman:20
--comment: Table Rattrapage pour gérer les rattrapages d'évaluations
CREATE TABLE Rattrapage
(
    idRattrapage    SERIAL PRIMARY KEY,
    idAbsence       INT       NOT NULL,
    date_rattrapage TIMESTAMP,
    salle           TEXT,
    remarque        TEXT,
    statut          TEXT DEFAULT 'a_planifier' CHECK (statut IN ('a_planifier', 'planifie', 'effectue', 'annule')),
    date_creation   TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (idAbsence) REFERENCES Absence (idAbsence) ON DELETE CASCADE,
    UNIQUE(idAbsence)
);
--rollback DROP TABLE Rattrapage;

--changeset Roman:21
--comment: Ajout du champ type_refus pour distinguer refus définitif et refus avec ressoumission
ALTER TABLE Absence ADD COLUMN type_refus TEXT CHECK (type_refus IN ('definitif', 'ressoumission'));
--rollback ALTER TABLE Absence DROP COLUMN type_refus;

-- End of changelog


