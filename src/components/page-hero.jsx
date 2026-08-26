export function PageHero({ title, image }) {
  return (
    <section
      className="relative mt-[60px] flex min-h-[45vh] items-center justify-center bg-cover bg-center text-white"
      style={{ backgroundImage: `url('${image}')` }}
    >
      <div className="absolute inset-0 bg-black/50" />
      <div className="relative z-10 mx-auto w-[90%] max-w-4xl px-4 text-center [text-shadow:1px_1px_3px_rgba(0,0,0,0.5)]">
        <h1 className="text-balance text-3xl font-bold md:text-5xl">{title}</h1>
      </div>
    </section>
  );
}
