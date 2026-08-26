import Image from "next/image";
import { Package, Ship, HardHat, Truck } from "lucide-react";
import { CtaButton } from "@/components/cta-button";
import { LinkCard } from "@/components/link-card";

const SERVICES = [
  {
    href: "/transport-routier",
    image: "/images/service-image-1.png",
    title: "Transport Routier",
    description: "Transport sécurisé de marchandises à travers le pays.",
  },
  {
    href: "/devis",
    image: "/images/service-transport-conteneur.jpg",
    title: "Transport de Conteneurs",
    description: "Transport de conteneurs adapté à vos besoins logistiques.",
  },
  {
    href: "/devis",
    image: "/images/service-location-camions.jpg",
    title: "Location de Camions",
    description: "Location de camions pour tous vos besoins de transport.",
  },
  {
    href: "/devis",
    image: "/images/service-bennes-tp.jpg",
    title: "Approvisionnements par Bennes TP",
    description: "Bennes TP pour l'approvisionnement de vos chantiers et matériaux.",
  },
];

const REALISATIONS = [
  {
    image: "/images/realisation-transfert-cereales.jpg",
    category: "Manutention",
    icon: Package,
    title: "Opération transfert de céréales en sacs",
  },
  {
    image: "/images/realisation-empotage-ble.jpg",
    category: "Opérations Portuaires",
    icon: Ship,
    title: "Opération d'empotage de blé",
  },
  {
    image: "/images/realisation-manutention-ter.jpg",
    category: "Manutention & Levage",
    icon: HardHat,
    title: "Manutention, levage et transfert de matériels sur le TER",
  },
  {
    image: "/images/realisation-transport-arachide.jpg",
    category: "Transport",
    icon: Truck,
    title: "Transport pour la campagne arachidière",
  },
];

const FEATURES = [
  {
    href: "/fiabilite",
    image: "/images/feature-image-1.png",
    title: "Fiabilité & Sécurité",
    description: "Respect des délais et sécurité de vos marchandises.",
  },
  {
    href: "/technologie",
    image: "/images/feature-image-2.png",
    title: "Haute Technologie",
    description: "Suivi en temps réel et gestion optimisée.",
  },
  {
    href: "/support-client",
    image: "/images/feature-image-3.png",
    title: "Support Client",
    description: "Une équipe dédiée à votre écoute 24/7.",
  },
];

const PARTNERS = [
  { src: "/images/spintech.png", alt: "Logo Spintech" },
  { src: "/images/smartek.png", alt: "Logo Partenaire 1" },
  { src: "/images/comsec.png", alt: "Logo Partenaire 2" },
  { src: "/images/watero.png", alt: "Logo Partenaire 3" },
  { src: "/images/sierra-logistics.png", alt: "Logo Partenaire 4" },
];

const TESTIMONIALS = [
  {
    photo: "/images/placeholder-testimonial-1.png",
    quote:
      "Sierra Logistics a transformé notre chaîne d'approvisionnement. Leur fiabilité et leur professionnalisme sont inégalés au Sénégal. Les livraisons sont toujours à l'heure !",
    author: "Karim Diallo, Responsable Logistique chez COMSEC",
  },
  {
    photo: "/images/placeholder-testimonial-2.png",
    quote:
      "Le service client de Sierra Logistics est exceptionnel. Ils sont toujours disponibles pour répondre à nos questions et gérer nos demandes spéciales rapidement. Un vrai partenaire !",
    author: "Fatou Ndiaye Sarr, Directrice des Opérations à SpinTech",
  },
  {
    photo: "/images/placeholder-testimonial-1.png",
    quote:
      "Leur technologie de suivi nous donne une visibilité complète. Nous savons exactement où se trouvent nos marchandises à tout moment. Très rassurant.",
    author: "Adama Ndiaye, Directeur de Sierra Logistics",
  },
];

export default function HomePage() {
  return (
    <>
      <section
        id="hero"
        className="relative mt-[60px] flex min-h-[65vh] items-center bg-cover bg-center pt-20 text-center text-white"
        style={{ backgroundImage: "url('/images/hero-background.png')" }}
      >
        <div className="absolute inset-0 bg-black/50" />
        <div className="relative z-10 mx-auto w-[90%] max-w-6xl px-4 py-16 [text-shadow:1px_1px_3px_rgba(0,0,0,0.5)]">
          <h1 className="text-4xl font-bold leading-tight md:text-6xl">
            Solutions Logistiques
            <br />
            Fiables et Efficaces
          </h1>
          <p className="mt-5 text-lg font-bold md:text-xl">
            Votre partenaire de confiance pour tous vos besoins de transport
          </p>
        </div>
      </section>

      <section id="services" className="bg-muted py-16 text-center">
        <div className="mx-auto w-full max-w-[1600px] px-6 lg:px-10">
          <h2 className="section-heading">Nos Services</h2>
          <div className="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            {SERVICES.map((service) => (
              <LinkCard key={service.title} {...service} className="max-w-none" />
            ))}
          </div>
        </div>
      </section>

      <section id="realisations" className="mt-20 py-16 text-center">
        <div className="mx-auto w-full max-w-[1600px] px-6 lg:px-10">
          <p className="text-sm font-semibold uppercase tracking-widest text-brand-accent">
            Les projets déjà réalisés
          </p>
          <h2 className="section-heading mt-2">Nos Réalisations</h2>
          <div className="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            {REALISATIONS.map((item) => (
              <div
                key={item.title}
                className="group relative aspect-[4/3] overflow-hidden rounded-lg shadow-md"
              >
                <Image
                  src={item.image}
                  alt={item.title}
                  fill
                  sizes="(min-width: 1024px) 25vw, (min-width: 640px) 50vw, 100vw"
                  className="object-cover transition-transform duration-500 group-hover:scale-110"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-black/85 via-black/20 to-transparent" />
                <div className="absolute inset-x-0 bottom-0 p-5 text-left">
                  <span className="inline-flex items-center gap-1.5 rounded-full bg-brand-accent px-3 py-1 text-xs font-semibold text-brand-accent-foreground">
                    <item.icon className="size-3.5" />
                    {item.category}
                  </span>
                  <h3 className="mt-3 text-lg font-semibold leading-snug text-white [text-shadow:1px_1px_3px_rgba(0,0,0,0.6)]">
                    {item.title}
                  </h3>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section id="pourquoi" className="mt-20 py-16 text-center">
        <div className="mx-auto w-[90%] max-w-6xl">
          <h2 className="section-heading">Pourquoi Choisir Sierra Logistics ?</h2>
          <div className="mt-10 flex flex-wrap justify-center gap-8">
            {FEATURES.map((feature) => (
              <LinkCard key={feature.href} {...feature} muted />
            ))}
          </div>
        </div>
      </section>

      <section id="quick-quote" className="mt-16 bg-muted py-16 text-center">
        <div className="mx-auto w-[90%] max-w-6xl">
          <h2 className="section-heading">Obtenez Votre Devis Rapidement</h2>
          <p className="mx-auto mt-4 max-w-2xl text-lg text-muted-foreground">
            Besoin d&apos;un devis pour votre transport ou votre solution logistique ?
            <br />
            Cliquez ci-dessous pour remplir notre formulaire détaillé et obtenir une
            proposition personnalisée.
          </p>
          <CtaButton href="/devis" className="mt-8">
            Demander un Devis Maintenant
          </CtaButton>
        </div>
      </section>

      <section id="partners" className="mt-16 py-16 text-center">
        <div className="mx-auto w-[90%] max-w-6xl">
          <h2 className="section-heading">Ils nous font confiance</h2>
          <div className="mt-10 flex flex-wrap items-center justify-center gap-8">
            {PARTNERS.map((partner) => (
              <Image
                key={partner.src}
                src={partner.src}
                alt={partner.alt}
                width={150}
                height={60}
                className="h-[60px] w-auto max-w-[150px] object-contain opacity-60 transition-opacity hover:opacity-100"
              />
            ))}
          </div>
        </div>
      </section>

      <section id="testimonials" className="bg-muted py-20 text-center">
        <div className="mx-auto w-[90%] max-w-6xl">
          <h2 className="section-heading mb-12">Ce que disent nos clients</h2>
          <div className="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
            {TESTIMONIALS.map((t, i) => (
              <div key={i} className="relative rounded-lg bg-background p-8 shadow-md">
                <Image
                  src={t.photo}
                  alt="Photo Client"
                  width={80}
                  height={80}
                  className="-mt-[70px] mx-auto mb-5 size-20 rounded-full border-4 border-background object-cover shadow-md"
                />
                <p className="mb-4 italic text-muted-foreground">&ldquo;{t.quote}&rdquo;</p>
                <p className="font-semibold text-primary">{t.author}</p>
              </div>
            ))}
          </div>
        </div>
      </section>
    </>
  );
}
