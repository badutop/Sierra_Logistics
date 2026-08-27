export const metadata = {
  title: {
    default: "Espace Admin",
    template: "%s - Espace Admin Sierra Logistics",
  },
  robots: { index: false, follow: false },
};

export default function AdminRootLayout({ children }) {
  return <div className="min-h-full bg-muted">{children}</div>;
}
