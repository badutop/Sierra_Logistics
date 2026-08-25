import { Suspense } from "react";
import { FactureProformaView } from "./facture-proforma-view";

export const metadata = {
  title: "Facture Proforma",
  robots: { index: false },
};

export default function FactureProformaPage() {
  return (
    <Suspense fallback={null}>
      <FactureProformaView />
    </Suspense>
  );
}
