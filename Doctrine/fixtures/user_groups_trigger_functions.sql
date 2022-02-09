CREATE OR REPLACE FUNCTION raise_user_groups_table_exception() RETURNS trigger as
$raise_user_groups_table_exception$
BEGIN
    RAISE EXCEPTION 'Unable to delete, create or update a row in the `user_group` table';
END;
$raise_user_groups_table_exception$ LANGUAGE plpgsql;