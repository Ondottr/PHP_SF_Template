CREATE OR REPLACE FUNCTION prevent_user_groups_modification()
    RETURNS TRIGGER AS
$$
BEGIN
    RAISE EXCEPTION 'Modifying the "user_groups" table is not allowed.';
END;
$$
    LANGUAGE plpgsql;