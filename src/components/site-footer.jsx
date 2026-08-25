export function SiteFooter() {
  return (
    <footer className="bg-neutral-600 py-8 text-center text-sm text-neutral-100">
      <div className="mx-auto flex w-[90%] max-w-6xl flex-col items-center gap-2.5">
        <p>&copy; {new Date().getFullYear()} Sierra Logistics. Tous droits réservés.</p>
        <div className="space-x-2.5">
          <a href="#" className="text-neutral-100 transition-colors hover:text-brand-accent">
            Politique de confidentialité
          </a>
          <span>|</span>
          <a href="#" className="text-neutral-100 transition-colors hover:text-brand-accent">
            Termes et Conditions
          </a>
        </div>
      </div>
    </footer>
  );
}
