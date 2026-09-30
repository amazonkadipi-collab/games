import type { Metadata } from "next";
import Image from "next/image";
import { getCatalog } from "@/lib/catalog";

type Props = { searchParams: Promise<{ q?: string }> };

export async function generateMetadata({ searchParams }: Props): Promise<Metadata> {
  const { q } = await searchParams;
  const term = q?.trim() || "games";
  return {
    title: `Search: ${term} - Free Online Games`,
    description: `Search free online games for ${term}. Discover browser games by title, category and tags.`,
    robots: { index: false, follow: true },
  };
}

export default async function SearchPage({ searchParams }: Props) {
  const { q = "" } = await searchParams;
  const term = q.trim().toLowerCase();
  const games = await getCatalog();
  const results = term
    ? games.filter((game) =>
        [game.title, game.description, game.category, ...game.tags]
          .join(" ")
          .toLowerCase()
          .includes(term)
      )
    : games;

  return (
    <main className="container" style={{ padding: "42px 0 70px" }}>
      <a href="/" style={{ color: "var(--muted)", fontSize: 13 }}>← Home</a>
      <h1 style={{ fontSize: "clamp(30px,4vw,48px)", margin: "22px 0 10px" }}>
        {term ? `Search results for “${q.trim()}”` : "Search games"}
      </h1>
      <form className="searchWrap" action="/search" style={{ maxWidth: 680, marginTop: 22 }}>
        <input className="search" name="q" defaultValue={q} placeholder="Search games, genres or tags..." aria-label="Search games" />
      </form>
      <p style={{ color: "var(--muted)", marginTop: 18 }}>
        {results.length} {results.length === 1 ? "game" : "games"} found
      </p>
      {results.length ? (
        <div className="grid" style={{ marginTop: 28 }}>
          {results.map((game) => (
            <a className="card" href={`/game/${game.slug}`} key={game.id || game.slug}>
              <div className="thumb">
                {game.thumbnail ? <Image src={game.thumbnail} alt={game.title} width={640} height={360} loading="lazy" /> : <span>{game.title}</span>}
              </div>
              <div className="cardBody">
                <div className="cardTitle">{game.title}</div>
                <div className="meta">{game.category || "Online Game"} · Play now</div>
              </div>
            </a>
          ))}
        </div>
      ) : (
        <section className="seoIntro" style={{ marginTop: 28 }}>
          <h2>No games found</h2>
          <p>Try another title, genre or tag. Once the live GameMonetize feed is connected, search uses the full catalog automatically.</p>
        </section>
      )}
    </main>
  );
}
