CREATE OR REPLACE FUNCTION abs_non_justif(idEtu int) RETURNS int AS $$
DECLARE
    nb_absences int;
BEGIN;
    RETURN (SELECT count(*) FROM Absence WHERE idEtudiant = idEtu AND justifie = FALSE);
END;
$$ LANGUAGE plpgsql;