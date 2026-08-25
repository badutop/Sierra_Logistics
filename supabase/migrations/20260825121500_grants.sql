-- Les policies RLS ne suffisent pas seules : Postgres exige aussi un GRANT
-- de privilège au niveau table pour le rôle concerné (sinon "new row
-- violates row-level security policy" est levée même avec une policy
-- WITH CHECK (true), car aucune policy ne "matche" un rôle sans privilège).
grant usage on schema public to anon;

grant insert on vehicles to anon;
grant insert, select on quotes to anon;
-- commandes : aucun grant pour anon (accès exclusivement via la route
-- serveur en service_role, qui contourne RLS et les grants).
