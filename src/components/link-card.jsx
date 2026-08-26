import Link from "next/link";
import Image from "next/image";
import { cn } from "@/lib/utils";

export function LinkCard({ href, image, title, description, muted = false, className }) {
  return (
    <Link
      href={href}
      className={cn(
        "group block h-full max-w-[350px] flex-1 basis-[300px] overflow-hidden rounded-lg shadow-md transition-all hover:-translate-y-2 hover:shadow-xl",
        muted ? "bg-muted" : "bg-background",
        className
      )}
    >
      <Image
        src={image}
        alt={title}
        width={400}
        height={200}
        className="h-[200px] w-full object-cover"
      />
      <div className="flex flex-col justify-between gap-2 p-5 text-left">
        <h3 className="text-xl font-semibold text-primary">
          {title} <span className="ml-1 font-bold text-brand-accent group-hover:brightness-90">»</span>
        </h3>
        <p className="text-muted-foreground">{description}</p>
      </div>
    </Link>
  );
}
