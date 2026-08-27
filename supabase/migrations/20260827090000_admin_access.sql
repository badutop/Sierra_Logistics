-- Espace d'administration : table des comptes autorisés + accès en
-- lecture (et mise à jour ciblée) pour les utilisateurs Supabase Auth
-- authentifiés qui figurent dans cette table. La clé anon publique ne
-- gagne aucun nouveau privilège ici.

create table admins (
  id uuid primary key references auth.users (id) on delete cascade,
  name text,
  role text not null default 'agent' check (role in ('admin', 'agent')),
  created_at timestamptz not null default now()
);

alter table admins enable row level security;

create policy "admins can read own row" on admins
  for select to authenticated
  using (id = auth.uid());

create policy "admins can read all quotes" on quotes
  for select to authenticated
  using (exists (select 1 from admins where admins.id = auth.uid()));

create policy "admins can read all vehicles" on vehicles
  for select to authenticated
  using (exists (select 1 from admins where admins.id = auth.uid()));

create policy "admins can update vehicles" on vehicles
  for update to authenticated
  using (exists (select 1 from admins where admins.id = auth.uid()));

create policy "admins can read all commandes" on commandes
  for select to authenticated
  using (exists (select 1 from admins where admins.id = auth.uid()));

grant usage on schema public to authenticated;
grant select on admins to authenticated;
grant select on quotes to authenticated;
grant select on vehicles to authenticated;
grant update on vehicles to authenticated;
grant select on commandes to authenticated;

-- Pour créer le premier administrateur :
-- 1. Supabase Dashboard -> Authentication -> Add user (email + mot de passe).
-- 2. SQL Editor :
--    insert into admins (id, name, role)
--    values ('<uuid de l''utilisateur créé>', 'Nom', 'admin');
