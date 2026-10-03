import { Suspense } from "react";
import { FactureDefinitiveRedirect } from "./facture-definitive-redirect";

export const metadata = {
  title: "Facture Définitive",
  robots: { index: false },
};

export default function FactureDefinitivePage() {
  return (
    <Suspense fallback={null}>
      <FactureDefinitiveRedirect />
    </Suspense>
  );
}
