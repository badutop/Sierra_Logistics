// Normalise un numéro sénégalais (avec ou sans indicatif, espaces, +...) au
// format attendu par wa.me (indicatif + numéro, chiffres uniquement).
export function toWhatsAppLink(phone, message) {
  const digits = (phone || "").replace(/\D/g, "");
  let intl = digits;

  if (digits.startsWith("221")) {
    intl = digits;
  } else if (digits.startsWith("0") && digits.length === 10) {
    intl = `221${digits.slice(1)}`;
  } else if (digits.length === 9) {
    intl = `221${digits}`;
  }

  return `https://wa.me/${intl}?text=${encodeURIComponent(message)}`;
}
