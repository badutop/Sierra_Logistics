"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import { Loader } from "lucide-react";
import { useAdminSession } from "@/lib/useAdminSession";
import { AdminShell } from "@/components/admin/admin-shell";

export default function DashboardLayout({ children }) {
  const router = useRouter();
  const { isLoading, isAdmin, admin } = useAdminSession();

  useEffect(() => {
    if (!isLoading && !isAdmin) {
      router.replace("/admin/login");
    }
  }, [isLoading, isAdmin, router]);

  if (isLoading || !isAdmin) {
    return (
      <div className="flex min-h-full items-center justify-center py-24">
        <Loader className="size-6 animate-spin text-brand-accent" />
      </div>
    );
  }

  return <AdminShell admin={admin}>{children}</AdminShell>;
}
