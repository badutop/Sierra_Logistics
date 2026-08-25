import Link from "next/link";
import { cn } from "@/lib/utils";

export function CtaButton({ href, children, className }) {
  return (
    <Link
      href={href}
      className={cn(
        "inline-block rounded-md bg-brand-accent px-6 py-3 font-semibold text-brand-accent-foreground transition-colors hover:bg-brand-accent/90",
        className
      )}
    >
      {children}
    </Link>
  );
}
