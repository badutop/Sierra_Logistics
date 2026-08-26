import Image from "next/image";
import { PageHero } from "@/components/page-hero";
import { CtaButton } from "@/components/cta-button";
import { BackHomeLink } from "@/components/back-home-link";

export function ServicePage({
  title,
  heroImage,
  heading,
  sideImage,
  sideImageAlt,
  sideImages,
  ctaHref,
  ctaLabel,
  children,
}) {
  return (
    <>
      <PageHero title={title} image={heroImage} />
      <section className="mx-auto w-[90%] max-w-6xl py-16 text-left">
        <BackHomeLink />
        <h2 className="mb-10 text-center text-3xl font-bold text-primary md:text-4xl">
          {heading}
        </h2>
        <div className="flex flex-wrap items-start gap-10">
          <div className="min-w-[300px] flex-[2]">
            <div className="mb-5 text-left">
              <Image
                src="/images/sierra-logistics.png"
                alt="Logo Sierra Logistics"
                width={200}
                height={60}
                className="float-left mr-5 mb-2.5 h-[60px] w-auto object-contain"
              />
            </div>
            <div className="space-y-4 text-foreground [&_li]:ml-5 [&_li]:list-disc [&_p]:leading-relaxed [&_ul]:space-y-2">
              {children}
            </div>
            {ctaHref && (
              <CtaButton href={ctaHref} className="mt-5">
                {ctaLabel}
              </CtaButton>
            )}
          </div>
          <div className="min-w-[200px] flex-1">
            {sideImages ? (
              <div className="relative pr-8 pb-8">
                <Image
                  src={sideImages[0].src}
                  alt={sideImages[0].alt}
                  width={500}
                  height={420}
                  className="w-full rounded-lg object-cover shadow-md"
                />
                <Image
                  src={sideImages[1].src}
                  alt={sideImages[1].alt}
                  width={320}
                  height={180}
                  className="absolute -bottom-0 -right-0 w-2/3 rounded-lg border-4 border-background object-cover shadow-xl"
                />
              </div>
            ) : (
              <Image
                src={sideImage}
                alt={sideImageAlt}
                width={500}
                height={350}
                className="w-full rounded-lg object-cover shadow-md"
              />
            )}
          </div>
        </div>
      </section>
    </>
  );
}
