CREATE OR REPLACE FUNCTION prevent_admin_deletion()
RETURNS TRIGGER AS $$
BEGIN
    IF OLD.id = 1 THEN
        RAISE EXCEPTION 'Deletion of administrator with ID 1 is not allowed.';
    END IF;
    RETURN OLD;
END;
$$ LANGUAGE plpgsql;
