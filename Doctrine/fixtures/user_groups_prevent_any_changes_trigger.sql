CREATE TRIGGER prevent_user_groups_modification
    BEFORE INSERT OR UPDATE OR DELETE ON user_groups
    FOR EACH STATEMENT
EXECUTE FUNCTION prevent_user_groups_modification();