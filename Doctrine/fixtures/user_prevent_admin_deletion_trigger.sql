CREATE TRIGGER prevent_admin_deletion
    BEFORE DELETE ON users
    FOR EACH ROW
EXECUTE FUNCTION prevent_admin_deletion();
