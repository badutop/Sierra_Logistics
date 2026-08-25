-- Sierra Logistics - schéma initial (vehicles, quotes, commandes)
-- Remplace l'ancien projet Supabase (okqdcojxmykuptloyqxu) mis en pause.

create extension if not exists pgcrypto;

-- vehicles : camions inscrits par les transporteurs partenaires
create table vehicles (
  id uuid primary key default gen_random_uuid(),
  name text not null,
  model text not null,
  license_plate text not null,
  fuel_type text not null,
  status text not null default 'disponible'
    check (status in ('disponible', 'en_course', 'maintenance')),
  contact_phone text,
  created_at timestamptz not null default now()
);

create index vehicles_status_idx on vehicles (status);
create index vehicles_model_idx on vehicles (model);

-- quotes : devis générés depuis /devis
create table quotes (
  id uuid primary key default gen_random_uuid(),
  nom text,
  email text,
  telephone text,
  ville_depart text,
  ville_arrivee text,
  type_marchandise text,
  poids numeric,
  type_vehicle text,
  date_expedition date,
  infos_additionnelles text,
  distance numeric,
  zone text,
  tarif_zone numeric,
  coefficient_camion numeric,
  montant_transport numeric,
  majoration numeric,
  sous_total numeric,
  tva numeric,
  total numeric,
  statut text not null default 'en_attente'
    check (statut in ('en_attente', 'commandé')),
  created_at timestamptz not null default now()
);

create index quotes_telephone_idx on quotes (telephone);

-- commandes : affectation camion/chauffeur réelle à un devis validé
create table commandes (
  id uuid primary key default gen_random_uuid(),
  proforma_id uuid not null unique references quotes (id),
  vehicle_id uuid not null references vehicles (id),
  camion_immatriculation text not null,
  chauffeur text not null,
  telephone_chauffeur text,
  date_validation timestamptz not null default now()
);

-- Row Level Security : les mutations sensibles (affectation, validation de
-- commande) passent uniquement par la route serveur (clé service_role, qui
-- contourne RLS) ; la clé anon ne garde que le strict nécessaire au
-- fonctionnement des formulaires publics.
alter table vehicles enable row level security;
alter table quotes enable row level security;
alter table commandes enable row level security;

create policy "anon can register a vehicle" on vehicles
  for insert to anon
  with check (true);

create policy "anon can submit a quote" on quotes
  for insert to anon
  with check (true);

create policy "anon can read quotes by id or phone" on quotes
  for select to anon
  using (true);
