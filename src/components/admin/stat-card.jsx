export function StatCard({ icon: Icon, label, value, hint }) {
  return (
    <div className="rounded-lg border-t-4 border-t-brand-accent bg-background p-5 shadow-sm">
      <div className="flex items-center gap-3">
        <span className="flex size-10 shrink-0 items-center justify-center rounded-full bg-brand-accent/10 text-brand-accent">
          <Icon className="size-5" />
        </span>
        <div>
          <p className="text-2xl font-bold text-primary">{value}</p>
          <p className="text-sm text-muted-foreground">{label}</p>
        </div>
      </div>
      {hint && <p className="mt-3 text-xs text-muted-foreground">{hint}</p>}
    </div>
  );
}
