import { cn } from "@/lib/utils";

export function FormMessage({ type = "success", children }) {
  return (
    <div
      className={cn(
        "mt-5 rounded-md border p-4 text-center font-semibold",
        type === "success" && "border-green-200 bg-green-50 text-green-700",
        type === "error" && "border-red-200 bg-red-50 text-red-700"
      )}
    >
      {children}
    </div>
  );
}
