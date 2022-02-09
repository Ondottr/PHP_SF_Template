CREATE TRIGGER user_group_disable_any_changes_trigger
    BEFORE DELETE OR INSERT OR UPDATE OR TRUNCATE
    ON public.user_groups
EXECUTE FUNCTION raise_user_groups_table_exception();